<?php

use InstagramAPI\Exception\SettingsException;
use InstagramAPI\Settings\StorageInterface;


class CustomHandler implements StorageInterface
{
	/** @var array A cache of important columns from the user's database row. */
	protected $_cache;

	private function getAccountInf( $userName )
	{
		$accountInf = FSwpFetch('account_sessions' , ['driver'=>'instagram' , 'username' => $userName]);
		return $accountInf;
	}

	/**
	 * Connect to a storage location and perform necessary startup preparations.
	 *
	 * {@inheritdoc}
	 */
	public function openLocation(
		array $locationConfig)
	{

	}

	/**
	 * Automatically writes to the correct user's row and caches the new value.
	 *
	 * @param string $column The database column.
	 * @param string $data   Data to be written.
	 *
	 * @throws \InstagramAPI\Exception\SettingsException
	 */
	protected function _setUserColumn(
		$column,
		$data)
	{
		if ($column != 'settings' && $column != 'cookies') {
			throw new SettingsException(sprintf(
				'Attempt to write to illegal database column "%s".',
				$column
			));
		}

		// Update if the user row already exists, otherwise insert.
		if ($this->_cache['id'] !== null)
		{
			FSwpDB()->update(FSwpTable('account_sessions') , [$column => $data] , ['id' => $this->_cache['id']]);
		}
		else
		{
			if( !is_null($this->_username) )
			{
				FSwpDB()->insert(FSwpTable('account_sessions') , ['driver'=>'instagram' , 'username' => $this->_username, $column => $data] );
				$this->_cache['id'] = FSwpDB()->insert_id;
			}
		}

		// Cache the new value.
		$this->_cache[$column] = $data;
	}

	/**
	 * Whether the storage backend contains a specific user.
	 *
	 * {@inheritdoc}
	 */
	public function hasUser(
		$username)
	{
		return $this->getAccountInf( $username ) ? true : false;
	}

	/**
	 * Move the internal data for a username to a new username.
	 *
	 * {@inheritdoc}
	 */
	public function moveUser(
		$oldUsername,
		$newUsername)
	{
		try {
			// Verify that the old username exists.
			if (!$this->hasUser($oldUsername)) {
				throw new SettingsException(sprintf(
					'Cannot move non-existent user "%s".',
					$oldUsername
				));
			}

			// Verify that the new username does not exist.
			if ($this->hasUser($newUsername)) {
				throw new SettingsException(sprintf(
					'Refusing to overwrite existing user "%s".',
					$newUsername
				));
			}

			// Now attempt to rename the old username column to the new name.
			FSwpDB()->update(FSwpTable('account_sessions') , ['username' => $oldUsername] , ['driver'=>'instagram' , 'username' => $newUsername]);
		} catch (SettingsException $e) {
			throw $e; // Ugly but necessary to re-throw only our own messages.
		} catch (\Exception $e) {
			throw new SettingsException($this->_backendName.' Error: '.$e->getMessage());
		}
	}

	/**
	 * Delete all internal data for a given username.
	 *
	 * {@inheritdoc}
	 */
	public function deleteUser(
		$username)
	{
		FSwpDB()->delete( FSwpTable('account_sessions') , [ 'driver'=>'instagram' , 'username' => $username ] );
	}

	/**
	 * Open the data storage for a specific user.
	 *
	 * {@inheritdoc}
	 */
	public function openUser(
		$username)
	{
		$this->_username = $username;

		$accountInf = $this->getAccountInf( $username ) ;

		$this->_cache = [
			'id'       => $accountInf['id'],
			'settings' => $accountInf['settings'],
			'cookies'  => $accountInf['cookies'],
		];
	}

	/**
	 * Load all settings for the currently active user.
	 *
	 * {@inheritdoc}
	 */
	public function loadUserSettings()
	{
		$userSettings = [];

		if (!empty($this->_cache['settings'])) {
			$userSettings = @json_decode($this->_cache['settings'], true, 512, JSON_BIGINT_AS_STRING);
			if (!is_array($userSettings)) {
				throw new SettingsException(sprintf(
					'Failed to decode corrupt settings for account "%s".',
					$this->_username
				));
			}
		}

		return $userSettings;
	}

	/**
	 * Save the settings for the currently active user.
	 *
	 * {@inheritdoc}
	 */
	public function saveUserSettings(
		array $userSettings,
		$triggerKey)
	{
		// Store the settings as a JSON blob.
		$encodedData = json_encode($userSettings);
		$this->_setUserColumn('settings', $encodedData);
	}

	/**
	 * Whether the storage backend has cookies for the currently active user.
	 *
	 * {@inheritdoc}
	 */
	public function hasUserCookies()
	{
		return isset($this->_cache['cookies'])
			&& !empty($this->_cache['cookies']);
	}

	/**
	 * Get the cookiefile disk path (only if a file-based cookie jar is wanted).
	 *
	 * {@inheritdoc}
	 */
	public function getUserCookiesFilePath()
	{
		// NULL = We (the backend) will handle the cookie loading/saving.
		return null;
	}

	/**
	 * (Non-cookiefile) Load all cookies for the currently active user.
	 *
	 * {@inheritdoc}
	 */
	public function loadUserCookies()
	{
		return isset($this->_cache['cookies'])
			? $this->_cache['cookies']
			: null;
	}

	/**
	 * (Non-cookiefile) Save all cookies for the currently active user.
	 *
	 * {@inheritdoc}
	 */
	public function saveUserCookies(
		$rawData)
	{
		// Store the raw cookie data as-provided.
		$this->_setUserColumn('cookies', $rawData);
	}

	/**
	 * Close the settings storage for the currently active user.
	 *
	 * {@inheritdoc}
	 */
	public function closeUser()
	{
		$this->_username = null;
		$this->_cache = null;
	}

	/**
	 * Disconnect from a storage location and perform necessary shutdown steps.
	 *
	 * {@inheritdoc}
	 */
	public function closeLocation()
	{

	}
}
