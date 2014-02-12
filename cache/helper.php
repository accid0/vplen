<?php return array (
  'charset' => 'UTF-8',
  'repositories' => 'sessions,user,group,comment,attachment,post,tag,whom,userfollowed,admin,groupfollowed,search',
  'db_host' => 'localhost',
  'db_user' => 'vplusr',
  'db_pass' => 'RbEM77Ik77db2e828d2b28d27d',
  'db_name' => 'vpldb',
  'db_charset' => 'utf8',
  'sql_type' => 'mysql',
  'prefix' => '',
  'base_url' => 'http://www.vplen.com:80',
  'cache' => 
  array (
    'dir' => '/cache/',
    'persist' => true,
    'enabled' => true,
    'ttl' => NULL,
    'debug' => false,
    'id' => 0,
    'driver' => 'File\\Tag',
    'persistent_group' => 
    array (
    ),
  ),
  'router' => 
  array (
    'live' => false,
  ),
  'log' => 
  array (
    'type' => 'core',
    'display' => 0,
    'level' => 30719,
    'core' => 
    array (
      'enable' => 1,
      'html' => 0,
      'stream' => '/var/www/foundgo/data/www/vplen.com/accido/logs/core.log',
    ),
    'phpconsole' => 
    array (
      'password' => 'accido',
      'charset' => 'UTF-8',
      'ssl_only' => false,
      'allowed_ip_mask' => 
      array (
      ),
      'detect_trace_and_source' => true,
      'eval' => 
      array (
        'enable' => true,
        'shared_vars' => 
        array (
          'post' => '_POST',
        ),
        'base_dir' => 
        array (
          0 => '/var/www/foundgo/data/www/vplen.com/accido',
        ),
      ),
      'handler' => 
      array (
        'disable_olds' => false,
        'disable_errors' => false,
        'disable_exceptions' => false,
      ),
    ),
  ),
);
