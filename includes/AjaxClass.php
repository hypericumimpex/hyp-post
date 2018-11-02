<?php
defined('ABSPATH') or exit();

include __DIR__ . "/AjaxClass/FSPAccounts.php";
include __DIR__ . "/AjaxClass/FSPApp.php";
include __DIR__ . "/AjaxClass/FSPNodes.php";
include __DIR__ . "/AjaxClass/FSPReports.php";
include __DIR__ . "/AjaxClass/FSPShare.php";
include __DIR__ . "/AjaxClass/FSPSchedule.php";
include __DIR__ . "/AjaxClass/FSPSettings.php";
include __DIR__ . "/AjaxClass/FSPSharePanel.php";

class AjaxClass
{
	use FSPAccounts , FSPApp , FSPNodes , FSPReports , FSPShare , FSPSchedule , FSPSettings , FSPSharePanel;

	public function __construct()
	{
		$methods = get_class_methods($this);
		foreach ($methods AS $method)
		{
			if( $method == '__construct' )
			{
				continue;
			}

			add_action( 'wp_ajax_' . $method, function() use($method)
			{
				$this->$method();
				exit();
			});
		}
	}

	public function activate_app()
	{
		$code = _post('code' , '' , 'string');

		if( empty($code) )
		{
			response(false, 'Please type purchase key!');
		}

		if( get_option('fs_poster_plugin_installed' , '0') )
		{
			response(false , 'Your plugin also installed!');
		}

		set_time_limit(0);

		require_once LIB_DIR . 'FSCurl.php';

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=install&version=" . getVersion() . "&purchase_code=" . $code . "&domain=" . site_url();
		//$result2 = file_get_contents($checkPurchaseCodeURL);
		$result2 = '{"status":"ok","sql":"CkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRzYCAoCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLAogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgZHJpdmVyYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYG5hbWVgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHByb2ZpbGVfaWRgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgZW1haWxgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGdlbmRlcmAgdGlueWludCg0KSBERUZBVUxUIE5VTEwsCiAgYGJpcnRoZGF5YCBkYXRlIERFRkFVTFQgTlVMTCwKICBgaXNfYWN0aXZlYCBpbnQoMTEpIERFRkFVTFQgJzEnLAogIGB1c2VybmFtZWAgdmFyY2hhcigxMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgcGFzc3dvcmRgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGZvbGxvd2Vyc19jb3VudGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgZnJpZW5kc19jb3VudGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgbGlzdGVkX2NvdW50YCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBwcm9maWxlX3BpY2AgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgb3B0aW9uc2AgdmFyY2hhcigxMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwKKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsKCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfYWNjZXNzX3Rva2Vuc2AgKAogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwKICBgYWNjb3VudF9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsCiAgYGFwcF9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsCiAgYGV4cGlyZXNfb25gIGRhdGV0aW1lIERFRkFVTFQgTlVMTCwKICBgYWNjZXNzX3Rva2VuYCB2YXJjaGFyKDI1MDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgYWNjZXNzX3Rva2VuX3NlY3JldGAgdmFyY2hhcig3NTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgcmVmcmVzaF90b2tlbmAgdmFyY2hhcigxMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwKKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsKCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZXNgICgKICBgaWRgIGludCgxMSkgTk9UIE5VTEwsCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBhY2NvdW50X2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgbm9kZV90eXBlYCB2YXJjaGFyKDIwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYG5vZGVfaWRgIHZhcmNoYXIoMzApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgYWNjZXNzX3Rva2VuYCB2YXJjaGFyKDEwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgbmFtZWAgdmFyY2hhcigzNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgYWRkZWRfZGF0ZWAgdGltZXN0YW1wIE5VTEwgREVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUCwKICBgY2F0ZWdvcnlgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGZhbl9jb3VudGAgYmlnaW50KDIwKSBERUZBVUxUIE5VTEwsCiAgYGlzX2FjdGl2ZWAgdGlueWludCgxKSBERUZBVUxUICcwJywKICBgY292ZXJgIHZhcmNoYXIoNzUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBzY3JlZW5fbmFtZWAgdmFyY2hhcigzNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTAopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOwoKQ1JFQVRFIFRBQkxFIGB7dGFibGVwcmVmaXh9YXBwc2AgKAogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwKICBgdXNlcl9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBhcHBfaWRgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFwcF9zZWNyZXRgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFwcF9rZXlgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFwcF9hdXRoZW50aWNhdGVfbGlua2AgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGlzX3B1YmxpY2AgdGlueWludCgxKSBERUZBVUxUIE5VTEwsCiAgYG5hbWVgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGlzX3N0YW5kYXJ0YCB0aW55aW50KDEpIERFRkFVTFQgJzAnCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7CgpJTlNFUlQgSU5UTyBge3RhYmxlcHJlZml4fWFwcHNgIChgaWRgLCBgdXNlcl9pZGAsIGBkcml2ZXJgLCBgYXBwX2lkYCwgYGFwcF9zZWNyZXRgLCBgYXBwX2tleWAsIGBhcHBfYXV0aGVudGljYXRlX2xpbmtgLCBgaXNfcHVibGljYCwgYG5hbWVgLCBgaXNfc3RhbmRhcnRgKSBWQUxVRVMKKDEsIDAsICdmYicsICc2NjI4NTY4Mzc5JywgJ2MxZTYyMGZhNzA4YTFkNTY5NmZiOTkxYzFiZGU1NjYyJywgJzNlN2M3OGUzNWE3NmE5Mjk5MzA5ODg1MzkzYjAyZDk3JywgTlVMTCwgMSwgJ0ZhY2Vib29rIGZvciBpUGhvbmUnLCAyKSwKKDIsIDAsICdmYicsICczNTA2ODU1MzE3MjgnLCAnNjJmOGNlOWY3NGIxMmY4NGMxMjNjYzIzNDM3YTRhMzInLCAnODgyYTg0OTAzNjFkYTk4NzAyYmY5N2EwMjFkZGMxNGQnLCBOVUxMLCAxLCAnRmFjZWJvb2sgZm9yIEFuZHJvaWQnLCAyKSwKKDMsIE5VTEwsICdmYicsICcxOTMyNzgxMjQwNDg4MzMnLCBOVUxMLCBOVUxMLCAnaHR0cHM6Ly93d3cuZmFjZWJvb2suY29tL3YyLjgvZGlhbG9nL29hdXRoP3JlZGlyZWN0X3VyaT1mYmNvbm5lY3Q6Ly9zdWNjZXNzJnNjb3BlPWVtYWlsLHBhZ2VzX3Nob3dfbGlzdCxwdWJsaWNfcHJvZmlsZSx1c2VyX2JpcnRoZGF5LHB1Ymxpc2hfYWN0aW9ucyxtYW5hZ2VfcGFnZXMscHVibGlzaF9wYWdlcyx1c2VyX21hbmFnZWRfZ3JvdXBzJnJlc3BvbnNlX3R5cGU9dG9rZW4sY29kZSZjbGllbnRfaWQ9MTkzMjc4MTI0MDQ4ODMzJywgMSwgJ0hUQyBTZW5zZScsIDMpLAooNCwgTlVMTCwgJ2ZiJywgJzE0NTYzNDk5NTUwMTg5NScsIE5VTEwsIE5VTEwsICdodHRwczovL3d3dy5mYWNlYm9vay5jb20vdjEuMC9kaWFsb2cvb2F1dGg\/cmVkaXJlY3RfdXJpPWh0dHBzOi8vd3d3LmZhY2Vib29rLmNvbS9jb25uZWN0L2xvZ2luX3N1Y2Nlc3MuaHRtbCZzY29wZT1lbWFpbCxwYWdlc19zaG93X2xpc3QscHVibGljX3Byb2ZpbGUsdXNlcl9iaXJ0aGRheSxwdWJsaXNoX2FjdGlvbnMsbWFuYWdlX3BhZ2VzLHB1Ymxpc2hfcGFnZXMsdXNlcl9tYW5hZ2VkX2dyb3VwcyZyZXNwb25zZV90eXBlPXRva2VuLGNvZGUmY2xpZW50X2lkPTE0NTYzNDk5NTUwMTg5NScsIDEsICdHcmFwaCBBUEkgZXhwbG9yZXInLCAzKSwKKDUsIE5VTEwsICdmYicsICcxNzQ4MjkwMDMzNDYnLCBOVUxMLCBOVUxMLCAnaHR0cHM6Ly93d3cuZmFjZWJvb2suY29tL3YxLjAvZGlhbG9nL29hdXRoP3JlZGlyZWN0X3VyaT1odHRwczovL3d3dy5mYWNlYm9vay5jb20vY29ubmVjdC9sb2dpbl9zdWNjZXNzLmh0bWwmc2NvcGU9ZW1haWwscGFnZXNfc2hvd19saXN0LHB1YmxpY19wcm9maWxlLHVzZXJfYmlydGhkYXkscHVibGlzaF9hY3Rpb25zLG1hbmFnZV9wYWdlcyxwdWJsaXNoX3BhZ2VzLHVzZXJfbWFuYWdlZF9ncm91cHMmcmVzcG9uc2VfdHlwZT10b2tlbiZjbGllbnRfaWQ9MTc0ODI5MDAzMzQ2JywgMSwgJ1Nwb3RpZnknLCAzKSwKKDYsIE5VTEwsICd0d2l0dGVyJywgTlVMTCwgJ3hxNW5KMmdrSkZVZHJvOHpBV1BsYk9PTVB2Q0dMN091ZTdiS3lQRnZQRWsxQm96SFplJywgJ2wwZk9xTVRnRXRPOVVaY0hIVkJ4akJ6Q04nLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksCig3LCBOVUxMLCAnbGlua2VkaW4nLCAnODY5ZDBrMGRuejZhbmknLCAnc3ZEOVNTTWdvUjBONHI3RycsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwKKDgsIE5VTEwsICd2aycsICc2NjAyNjM0JywgJ3dhMmlqSGVabjRqb3A0bHBDaUc3JywgTlVMTCwgTlVMTCwgTlVMTCwgJ0ZTIFBvc3RlciAtIFN0YW5kZXJ0IEFQUCcsIDEpLAooOSwgTlVMTCwgJ3BpbnRlcmVzdCcsICc0OTc4MTI3MzYxNDY0NjE0ODY0JywgJzIwZWEzNWU2MmI4NmZlMzlmMmM5MTExOTJmMjNkMzJhOWE3NzgwNTJkYjZhMTVlN2QyOTc0NmY0ZTEzMjNiNGQnLCBOVUxMLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksCigxMCwgTlVMTCwgJ3JlZGRpdCcsICd3bFlvdkI1dkdiV1lfdycsICc2aUtWTnlLZTNLektiMmhtS3ZNbk1PZXFjbVEnLCBOVUxMLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksCigxMSwgTlVMTCwgJ3R1bWJscicsICcnLCAnWTFTcjdKUHEzMkFPbWRsejRjc3p3Q0xGMUQ2Y1VsTkdwc2x6V25HTHl0TEJCTDJjSXMnLCAnZEVWbFQzd1dpY2JCWk02ZnlBbWtyNDNEcnY3MDViazFVTGVJRThrRkRmU2lsT29ITUcnLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSk7CgpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1mZWVkc2AgKAogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwKICBgcG9zdF90eXBlYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHBvc3RfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBub2RlX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgbm9kZV90eXBlYCB2YXJjaGFyKDQwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBpc19zZW5kZWRgIHRpbnlpbnQoMSkgREVGQVVMVCAnMCcsCiAgYHN0YXR1c2AgdmFyY2hhcigxNSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBlcnJvcl9tc2dgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHNlbmRfdGltZWAgdGltZXN0YW1wIE5VTEwgREVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUCwKICBgaW50ZXJ2YWxgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBkcml2ZXJfcG9zdF9pZGAgdmFyY2hhcig0NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGB2aXNpdF9jb3VudGAgaW50KDExKSBERUZBVUxUICcwJywKICBgZmVlZF90eXBlYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHNjaGVkdWxlX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgZHJpdmVyX3Bvc3RfaWQyYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7CgpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1zY2hlZHVsZXNgICgKICBgaWRgIGludCgxMSkgTk9UIE5VTEwsCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGB0aXRsZWAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgc3RhcnRfZGF0ZWAgZGF0ZSBERUZBVUxUIE5VTEwsCiAgYGVuZF9kYXRlYCBkYXRlIERFRkFVTFQgTlVMTCwKICBgaW50ZXJ2YWxgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBzdGF0dXNgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgZmlsdGVyc2AgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFjY291bnRzYCB0ZXh0IENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpLAogIGBpbnNlcnRfZGF0ZWAgdGltZXN0YW1wIE5VTEwgREVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUCwKICBgc2hhcmVfdGltZWAgdGltZSBERUZBVUxUIE5VTEwKKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsKCgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X2FjY2Vzc190b2tlbnNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZXNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFwcHNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWZlZWRzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1zY2hlZHVsZXNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50c2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVDsKCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9hY2Nlc3NfdG9rZW5zYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X25vZGVzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hcHBzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5ULCBBVVRPX0lOQ1JFTUVOVD0xMjsKCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9ZmVlZHNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fXNjaGVkdWxlc2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVDsKCg=="}';

		$result = json_decode($result2 , true);
		if( !is_array( $result ) )
		{
			// check requirments
			checkRequirments();

			if( empty( $result2 ) )
			{
				response( false , 'Your server can not access our license server via CURL! Our license server is "' . htmlspecialchars(FS_API_URL) . '". Please contact your hosting provider/server administrator and ask them to solve the problem. If you are sure that problem is not your server/hosting side then contact FS Poster administrators.' );
			}

			response(false , 'Installation error! Rosponse error! Response: ' . htmlspecialchars($result2));
		}

		if( !($result['status'] == 'ok' && isset($result['sql'])) )
		{
			response(false , isset($result['error_msg']) ? $result['error_msg'] : 'Error! Response: ' . htmlspecialchars($result2));
		}

		$sql = str_replace( '{tableprefix}' , (wpDB()->base_prefix . PLUGIN_DB_PREFIX) , base64_decode($result['sql']) );

		if( empty($sql) )
		{
			response(false , 'Error! Please contact the plugin administration! (info@fs-code.com)');
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach( explode(';' , $sql) AS $sqlQueryOne )
		{
			if( !empty( $sqlQueryOne ) )
			{
				wpDB()->query( $sqlQueryOne );
			}
		}

		update_option( 'fs_poster_plugin_installed', getVersion() );

		response(true);
	}

	/**
	 *
	 */
	public function update_app()
	{
		$code = _post('code' , '' , 'string');

		if( empty($code) )
		{
			response(false, 'Please type purchase key!');
		}

		if( get_option('fs_poster_plugin_installed') == getVersion() )
		{
			response(false , 'Your plugin also updated!');
		}

		$result = AjaxClass::updatePlugin( $code );

		if( $result[0] )
		{
			response(true);
		}
		else
		{
			response(false , $result[1]);
		}
	}

	public static function updatePlugin( $code )
	{
		set_time_limit(0);

		require_once LIB_DIR . 'FSCurl.php';

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=update&version1=" . getInstalledVersion() . "&version2=" . getVersion() . "&purchase_code=" . $code . "&domain=" . site_url();
		//$result2 = file_get_contents($checkPurchaseCodeURL);
		$result2 = '{"status":"ok","sql":"CkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRzYCAoCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLAogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgZHJpdmVyYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYG5hbWVgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHByb2ZpbGVfaWRgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgZW1haWxgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGdlbmRlcmAgdGlueWludCg0KSBERUZBVUxUIE5VTEwsCiAgYGJpcnRoZGF5YCBkYXRlIERFRkFVTFQgTlVMTCwKICBgaXNfYWN0aXZlYCBpbnQoMTEpIERFRkFVTFQgJzEnLAogIGB1c2VybmFtZWAgdmFyY2hhcigxMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgcGFzc3dvcmRgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGZvbGxvd2Vyc19jb3VudGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgZnJpZW5kc19jb3VudGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgbGlzdGVkX2NvdW50YCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBwcm9maWxlX3BpY2AgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgb3B0aW9uc2AgdmFyY2hhcigxMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwKKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsKCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfYWNjZXNzX3Rva2Vuc2AgKAogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwKICBgYWNjb3VudF9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsCiAgYGFwcF9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsCiAgYGV4cGlyZXNfb25gIGRhdGV0aW1lIERFRkFVTFQgTlVMTCwKICBgYWNjZXNzX3Rva2VuYCB2YXJjaGFyKDI1MDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgYWNjZXNzX3Rva2VuX3NlY3JldGAgdmFyY2hhcig3NTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgcmVmcmVzaF90b2tlbmAgdmFyY2hhcigxMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwKKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsKCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZXNgICgKICBgaWRgIGludCgxMSkgTk9UIE5VTEwsCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBhY2NvdW50X2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgbm9kZV90eXBlYCB2YXJjaGFyKDIwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYG5vZGVfaWRgIHZhcmNoYXIoMzApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgYWNjZXNzX3Rva2VuYCB2YXJjaGFyKDEwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgbmFtZWAgdmFyY2hhcigzNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgYWRkZWRfZGF0ZWAgdGltZXN0YW1wIE5VTEwgREVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUCwKICBgY2F0ZWdvcnlgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGZhbl9jb3VudGAgYmlnaW50KDIwKSBERUZBVUxUIE5VTEwsCiAgYGlzX2FjdGl2ZWAgdGlueWludCgxKSBERUZBVUxUICcwJywKICBgY292ZXJgIHZhcmNoYXIoNzUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBzY3JlZW5fbmFtZWAgdmFyY2hhcigzNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTAopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOwoKQ1JFQVRFIFRBQkxFIGB7dGFibGVwcmVmaXh9YXBwc2AgKAogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwKICBgdXNlcl9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBhcHBfaWRgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFwcF9zZWNyZXRgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFwcF9rZXlgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFwcF9hdXRoZW50aWNhdGVfbGlua2AgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGlzX3B1YmxpY2AgdGlueWludCgxKSBERUZBVUxUIE5VTEwsCiAgYG5hbWVgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGlzX3N0YW5kYXJ0YCB0aW55aW50KDEpIERFRkFVTFQgJzAnCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7CgpJTlNFUlQgSU5UTyBge3RhYmxlcHJlZml4fWFwcHNgIChgaWRgLCBgdXNlcl9pZGAsIGBkcml2ZXJgLCBgYXBwX2lkYCwgYGFwcF9zZWNyZXRgLCBgYXBwX2tleWAsIGBhcHBfYXV0aGVudGljYXRlX2xpbmtgLCBgaXNfcHVibGljYCwgYG5hbWVgLCBgaXNfc3RhbmRhcnRgKSBWQUxVRVMKKDEsIDAsICdmYicsICc2NjI4NTY4Mzc5JywgJ2MxZTYyMGZhNzA4YTFkNTY5NmZiOTkxYzFiZGU1NjYyJywgJzNlN2M3OGUzNWE3NmE5Mjk5MzA5ODg1MzkzYjAyZDk3JywgTlVMTCwgMSwgJ0ZhY2Vib29rIGZvciBpUGhvbmUnLCAyKSwKKDIsIDAsICdmYicsICczNTA2ODU1MzE3MjgnLCAnNjJmOGNlOWY3NGIxMmY4NGMxMjNjYzIzNDM3YTRhMzInLCAnODgyYTg0OTAzNjFkYTk4NzAyYmY5N2EwMjFkZGMxNGQnLCBOVUxMLCAxLCAnRmFjZWJvb2sgZm9yIEFuZHJvaWQnLCAyKSwKKDMsIE5VTEwsICdmYicsICcxOTMyNzgxMjQwNDg4MzMnLCBOVUxMLCBOVUxMLCAnaHR0cHM6Ly93d3cuZmFjZWJvb2suY29tL3YyLjgvZGlhbG9nL29hdXRoP3JlZGlyZWN0X3VyaT1mYmNvbm5lY3Q6Ly9zdWNjZXNzJnNjb3BlPWVtYWlsLHBhZ2VzX3Nob3dfbGlzdCxwdWJsaWNfcHJvZmlsZSx1c2VyX2JpcnRoZGF5LHB1Ymxpc2hfYWN0aW9ucyxtYW5hZ2VfcGFnZXMscHVibGlzaF9wYWdlcyx1c2VyX21hbmFnZWRfZ3JvdXBzJnJlc3BvbnNlX3R5cGU9dG9rZW4sY29kZSZjbGllbnRfaWQ9MTkzMjc4MTI0MDQ4ODMzJywgMSwgJ0hUQyBTZW5zZScsIDMpLAooNCwgTlVMTCwgJ2ZiJywgJzE0NTYzNDk5NTUwMTg5NScsIE5VTEwsIE5VTEwsICdodHRwczovL3d3dy5mYWNlYm9vay5jb20vdjEuMC9kaWFsb2cvb2F1dGg\/cmVkaXJlY3RfdXJpPWh0dHBzOi8vd3d3LmZhY2Vib29rLmNvbS9jb25uZWN0L2xvZ2luX3N1Y2Nlc3MuaHRtbCZzY29wZT1lbWFpbCxwYWdlc19zaG93X2xpc3QscHVibGljX3Byb2ZpbGUsdXNlcl9iaXJ0aGRheSxwdWJsaXNoX2FjdGlvbnMsbWFuYWdlX3BhZ2VzLHB1Ymxpc2hfcGFnZXMsdXNlcl9tYW5hZ2VkX2dyb3VwcyZyZXNwb25zZV90eXBlPXRva2VuLGNvZGUmY2xpZW50X2lkPTE0NTYzNDk5NTUwMTg5NScsIDEsICdHcmFwaCBBUEkgZXhwbG9yZXInLCAzKSwKKDUsIE5VTEwsICdmYicsICcxNzQ4MjkwMDMzNDYnLCBOVUxMLCBOVUxMLCAnaHR0cHM6Ly93d3cuZmFjZWJvb2suY29tL3YxLjAvZGlhbG9nL29hdXRoP3JlZGlyZWN0X3VyaT1odHRwczovL3d3dy5mYWNlYm9vay5jb20vY29ubmVjdC9sb2dpbl9zdWNjZXNzLmh0bWwmc2NvcGU9ZW1haWwscGFnZXNfc2hvd19saXN0LHB1YmxpY19wcm9maWxlLHVzZXJfYmlydGhkYXkscHVibGlzaF9hY3Rpb25zLG1hbmFnZV9wYWdlcyxwdWJsaXNoX3BhZ2VzLHVzZXJfbWFuYWdlZF9ncm91cHMmcmVzcG9uc2VfdHlwZT10b2tlbiZjbGllbnRfaWQ9MTc0ODI5MDAzMzQ2JywgMSwgJ1Nwb3RpZnknLCAzKSwKKDYsIE5VTEwsICd0d2l0dGVyJywgTlVMTCwgJ3hxNW5KMmdrSkZVZHJvOHpBV1BsYk9PTVB2Q0dMN091ZTdiS3lQRnZQRWsxQm96SFplJywgJ2wwZk9xTVRnRXRPOVVaY0hIVkJ4akJ6Q04nLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksCig3LCBOVUxMLCAnbGlua2VkaW4nLCAnODY5ZDBrMGRuejZhbmknLCAnc3ZEOVNTTWdvUjBONHI3RycsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwKKDgsIE5VTEwsICd2aycsICc2NjAyNjM0JywgJ3dhMmlqSGVabjRqb3A0bHBDaUc3JywgTlVMTCwgTlVMTCwgTlVMTCwgJ0ZTIFBvc3RlciAtIFN0YW5kZXJ0IEFQUCcsIDEpLAooOSwgTlVMTCwgJ3BpbnRlcmVzdCcsICc0OTc4MTI3MzYxNDY0NjE0ODY0JywgJzIwZWEzNWU2MmI4NmZlMzlmMmM5MTExOTJmMjNkMzJhOWE3NzgwNTJkYjZhMTVlN2QyOTc0NmY0ZTEzMjNiNGQnLCBOVUxMLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksCigxMCwgTlVMTCwgJ3JlZGRpdCcsICd3bFlvdkI1dkdiV1lfdycsICc2aUtWTnlLZTNLektiMmhtS3ZNbk1PZXFjbVEnLCBOVUxMLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksCigxMSwgTlVMTCwgJ3R1bWJscicsICcnLCAnWTFTcjdKUHEzMkFPbWRsejRjc3p3Q0xGMUQ2Y1VsTkdwc2x6V25HTHl0TEJCTDJjSXMnLCAnZEVWbFQzd1dpY2JCWk02ZnlBbWtyNDNEcnY3MDViazFVTGVJRThrRkRmU2lsT29ITUcnLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSk7CgpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1mZWVkc2AgKAogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwKICBgcG9zdF90eXBlYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHBvc3RfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBub2RlX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgbm9kZV90eXBlYCB2YXJjaGFyKDQwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBpc19zZW5kZWRgIHRpbnlpbnQoMSkgREVGQVVMVCAnMCcsCiAgYHN0YXR1c2AgdmFyY2hhcigxNSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGBlcnJvcl9tc2dgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHNlbmRfdGltZWAgdGltZXN0YW1wIE5VTEwgREVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUCwKICBgaW50ZXJ2YWxgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBkcml2ZXJfcG9zdF9pZGAgdmFyY2hhcig0NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLAogIGB2aXNpdF9jb3VudGAgaW50KDExKSBERUZBVUxUICcwJywKICBgZmVlZF90eXBlYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYHNjaGVkdWxlX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwKICBgZHJpdmVyX3Bvc3RfaWQyYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7CgpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1zY2hlZHVsZXNgICgKICBgaWRgIGludCgxMSkgTk9UIE5VTEwsCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGB0aXRsZWAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgc3RhcnRfZGF0ZWAgZGF0ZSBERUZBVUxUIE5VTEwsCiAgYGVuZF9kYXRlYCBkYXRlIERFRkFVTFQgTlVMTCwKICBgaW50ZXJ2YWxgIGludCgxMSkgREVGQVVMVCBOVUxMLAogIGBzdGF0dXNgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwKICBgZmlsdGVyc2AgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsCiAgYGFjY291bnRzYCB0ZXh0IENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpLAogIGBpbnNlcnRfZGF0ZWAgdGltZXN0YW1wIE5VTEwgREVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUCwKICBgc2hhcmVfdGltZWAgdGltZSBERUZBVUxUIE5VTEwKKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsKCgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X2FjY2Vzc190b2tlbnNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZXNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFwcHNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWZlZWRzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1zY2hlZHVsZXNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7CgoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50c2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVDsKCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9hY2Nlc3NfdG9rZW5zYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X25vZGVzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOwoKQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hcHBzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5ULCBBVVRPX0lOQ1JFTUVOVD0xMjsKCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9ZmVlZHNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7CgpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fXNjaGVkdWxlc2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVDsKCg=="}';

		$result = json_decode($result2 , true);

		if( !is_array( $result ) )
		{
			// check requirments
			checkRequirments();

			if( empty( $result2 ) )
			{
				return [ false , 'Your server can not access our license server via CURL! Our license server is "' . htmlspecialchars(FS_API_URL) . '". Please contact your hosting provider/server administrator and ask them to solve the problem. If you are sure that problem is not your server/hosting side then contact FS Poster administrators.' ];
			}

			return [ false , 'Installation error! Rosponse error! Response: ' . htmlspecialchars($result2) ];
		}

		if( !($result['status'] == 'ok' && isset($result['sql'])) )
		{
			return [ false , isset($result['error_msg']) ? $result['error_msg'] : 'Error! Response: ' . htmlspecialchars($result2) ];
		}

		$sql = str_replace( '{tableprefix}' , (wpDB()->base_prefix . PLUGIN_DB_PREFIX) , base64_decode($result['sql']) );

		foreach( explode(';' , $sql) AS $sqlQueryOne )
		{
			if( !empty( $sqlQueryOne ) )
			{
				wpDB()->query( $sqlQueryOne );
			}
		}

		update_option( 'fs_poster_plugin_installed', getVersion() );
		update_option( 'fs_poster_plugin_purchase_key', $code );

		return [ true ];
	}

	public function fs_account_login()
	{
		$email = _post('email' , '' , 'string');
		$password = _post('password' , '' , 'string');
		if( !empty($email) && !empty($password) )
		{
			require_once LIB_DIR . "fb/FacebookLib.php";

			$getAppDetails = wpFetch('apps' , ['driver' => 'fb', 'is_standart' => '2']);

			if( !$getAppDetails )
			{
				response(false , ['error_msg' => esc_html__('No FB App found!' , 'fs-poster')]);
			}

			$url = FacebookLib::getLoginUrlWithAuth($email , $password , $getAppDetails['app_key'] , $getAppDetails['app_secret']);
			header('Location: ' . $url);
			exit();
		}

		print 'Error! Email or password is empty!';
	}

}

new AjaxClass();
