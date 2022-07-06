<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

// built-in routes
$route['default_controller'] = 'main';
$route['404_override'] = 'ps404';
$route['translate_uri_dashes'] = FALSE;

// redirect for short url
$route['login'] = "main/login";
$route['logout'] = "main/logout";
$route['reset_request'] = "main/reset_request";
$route['reset_email/(.*)'] = "main/reset_email/$1";
$route['verify-email/(.*)'] = "main/verify_email/$1";

$route['success_reset_password'] = "main/success_reset_password";
$route['remove-item-from-cart'] = "crons/remove_item_from_cart";
$route['track-order'] = "crons/update_order_status";
$route['expire-offer'] = "crons/expire_offer";
$route['read-xcel'] = "crons/read_xcel";
$route['inactive-coupon'] = "crons/inactive_coupon";
$route['notify-expire-item'] = "crons/notify_expireitem_user";
$route['expire-item'] = "crons/expire_item";
$route['order-complete'] = "crons/order_complete";
$route['transfer-amount'] = "crons/transfer_amount";
$route['cancel-order'] = "crons/cancel_order";
$route['cancel-dispute-request'] = "crons/cancel_dispute_request";
$route['refund-buyer'] = "crons/refund_buyer";

// if both backend and frontend exist,
$route['admin'] = "backend/dashboard";
$route['admin/(.*)'] = "backend/$1";
$route['rest/card/(.*)'] = "rest/cards/$1";
$route['rest/(.*)'] = "rest/$1";
$route['guestajax/(.*)'] = "frontend/guestajax/$1";
$route['userajax/(.*)'] = "frontend/userajax/$1";
$route['(.*)'] = "frontend/home/$1";
// $route['(.*)'] = "backend/$1";
