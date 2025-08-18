<?php

use Illuminate\Support\Facades\Route;

// Routes for Admin
require __DIR__.'/admin/admin.php';

// Routes for Client
require __DIR__.'/client/auth.php';
require __DIR__.'/client/client.php';
