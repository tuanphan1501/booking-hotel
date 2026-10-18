<?php

use Illuminate\Support\Facades\Route;

// The public site is the Mobile App; the web root opens the Web Admin.
Route::redirect('/', '/admin');
