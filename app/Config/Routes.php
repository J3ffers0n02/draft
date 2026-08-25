<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('track', 'Home::index');
$routes->get('track/search', 'Home::search');
$routes->get('admin/login', 'Admin::login');
$routes->post('admin/login', 'Admin::attemptLogin');
$routes->get('admin/logout', 'Admin::logout');
$routes->get('admin/profile', 'Admin::profile');
$routes->post('admin/profile', 'Admin::updateProfile');
$routes->post('admin/accounts', 'Admin::createAccount');
$routes->get('admin', 'Admin::dashboard');
$routes->get('admin/client-orders', 'Admin::clientOrders');
$routes->post('admin/orders', 'Admin::createOrder');
$routes->post('admin/orders/(:num)/edit', 'Admin::editOrder/$1');
$routes->post('admin/orders/(:num)/status', 'Admin::updateStatus/$1');
$routes->post('admin/orders/(:num)/payment-status', 'Admin::updatePaymentStatus/$1');
$routes->post('admin/orders/(:num)/delete', 'Admin::deleteOrder/$1');
