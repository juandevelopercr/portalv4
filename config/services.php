<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Third Party Services
  |--------------------------------------------------------------------------
  |
  | This file is for storing the credentials for third party services such
  | as Mailgun, Postmark, AWS and more. This file provides the de facto
  | location for this type of information, allowing packages to have
  | a conventional file to locate the various service credentials.
  |
  */

  'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
  ],

  'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
  ],

  'slack' => [
    'notifications' => [
      'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
      'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
    ],
  ],

  'bccr' => [
    //'token' => env('BCCR_API_TOKEN', 'AI9RRO74HA'),
    'token' => env('BCCR_API_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJCQ0NSLVNEREUiLCJzdWIiOiJocm9tYW5jckBnbWFpbC5jb20iLCJhdWQiOiJTRERFLVNpdGlvRXh0ZXJubyIsImV4cCI6MjUzNDAyMzAwODAwLCJuYmYiOjE3NjM2NTYwMTcsImlhdCI6MTc2MzY1NjAxNywianRpIjoiYjkyZTQ1NTYtNTMzZC00MWM0LTk4OGUtNDAxYzdhOTVhZjBjIiwiZW1haWwiOiJocm9tYW5jckBnbWFpbC5jb20ifQ.iSLnO7RXVTeb7uqdD501tkVlfiOJf8UvQARkEO29wVI'),
    'email' => env('BCCR_API_EMAIL', 'hromancr@gmail.com'),
    'nombre' => env('BCCR_API_NOMBRE', 'Henry'),
  ],

];
