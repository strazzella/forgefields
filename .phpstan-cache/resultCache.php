<?php declare(strict_types = 1);

return [
	'lastFullAnalysisTime' => 1767023099,
	'meta' => array (
  'cacheVersion' => 'v12-linesToIgnore',
  'phpstanVersion' => '1.12.32',
  'phpVersion' => 80228,
  'projectConfig' => '{parameters: {level: 5, paths: [C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\forge-fields.php, C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes], tmpDir: C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\.phpstan-cache, bootstrapFiles: [C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\phpstan-bootstrap.php, C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\phpstan-wp-classes.php]}}',
  'analysedPaths' => 
  array (
    0 => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\forge-fields.php',
    1 => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes',
  ),
  'scannedFiles' => 
  array (
  ),
  'composerLocks' => 
  array (
    'C:/Users/vince/Desktop/Projects/hire-prod/hire-niagara/public/blog/wp-content/plugins/forge-fields/composer.lock' => 'a679f731e093260b4b6521421c1530d5e1640a30',
  ),
  'composerInstalled' => 
  array (
    'C:/Users/vince/Desktop/Projects/hire-prod/hire-niagara/public/blog/wp-content/plugins/forge-fields/vendor/composer/installed.php' => 
    array (
      'versions' => 
      array (
        'php-stubs/wordpress-stubs' => 
        array (
          'pretty_version' => 'v6.9.0',
          'version' => '6.9.0.0',
          'reference' => '5171cb6650e6c583a96943fd6ea0dfa3e1089a8a',
          'type' => 'library',
          'install_path' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\vendor\\composer/../php-stubs/wordpress-stubs',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpstan/phpstan' => 
        array (
          'pretty_version' => '1.12.32',
          'version' => '1.12.32.0',
          'reference' => '2770dcdf5078d0b0d53f94317e06affe88419aa8',
          'type' => 'library',
          'install_path' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\vendor\\composer/../phpstan/phpstan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
      ),
    ),
  ),
  'executedFilesHashes' => 
  array (
    'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\phpstan-bootstrap.php' => 'ba641fe1c9e26527a5b54b81339383821c37905b',
    'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\phpstan-wp-classes.php' => 'e49b5b8ef1f34aab840a1372375fe5bcf9e5d585',
    'phar://C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\Attribute.php' => 'eaf9127f074e9c7ebc65043ec4050f9fed60c2bb',
    'phar://C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\ReflectionAttribute.php' => '0b4b78277eb6545955d2ce5e09bff28f1f8052c8',
    'phar://C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\ReflectionIntersectionType.php' => 'a3e6299b87ee5d407dae7651758edfa11a74cb11',
    'phar://C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\ReflectionUnionType.php' => '1b349aa997a834faeafe05fa21bc31cae22bf2e2',
  ),
  'phpExtensions' => 
  array (
    0 => 'Core',
    1 => 'PDO',
    2 => 'Phar',
    3 => 'Reflection',
    4 => 'SPL',
    5 => 'SimpleXML',
    6 => 'bcmath',
    7 => 'calendar',
    8 => 'ctype',
    9 => 'curl',
    10 => 'date',
    11 => 'dom',
    12 => 'exif',
    13 => 'fileinfo',
    14 => 'filter',
    15 => 'gd',
    16 => 'hash',
    17 => 'iconv',
    18 => 'intl',
    19 => 'json',
    20 => 'libxml',
    21 => 'mbstring',
    22 => 'mysqli',
    23 => 'mysqlnd',
    24 => 'openssl',
    25 => 'pcre',
    26 => 'pdo_mysql',
    27 => 'random',
    28 => 'readline',
    29 => 'session',
    30 => 'standard',
    31 => 'tokenizer',
    32 => 'xml',
    33 => 'xmlreader',
    34 => 'xmlwriter',
    35 => 'xsl',
    36 => 'zip',
    37 => 'zlib',
  ),
  'stubFiles' => 
  array (
  ),
  'level' => '5',
),
	'projectExtensionFiles' => array (
),
	'errorsCallback' => static function (): array { return array (
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function current_user_can not found.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 407,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 407,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'If condition is always true.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 739,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 739,
       'nodeType' => 'PhpParser\\Node\\Stmt\\If_',
       'identifier' => 'if.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'If condition is always true.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 776,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 776,
       'nodeType' => 'PhpParser\\Node\\Stmt\\If_',
       'identifier' => 'if.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $restore_url might not be defined.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1094,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 1094,
       'nodeType' => 'PhpParser\\Node\\Expr\\Variable',
       'identifier' => 'variable.undefined',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $delete_url might not be defined.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1097,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 1097,
       'nodeType' => 'PhpParser\\Node\\Expr\\Variable',
       'identifier' => 'variable.undefined',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $dup_url might not be defined.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1106,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 1106,
       'nodeType' => 'PhpParser\\Node\\Expr\\Variable',
       'identifier' => 'variable.undefined',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $toggle_url might not be defined.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1109,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 1109,
       'nodeType' => 'PhpParser\\Node\\Expr\\Variable',
       'identifier' => 'variable.undefined',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $clear_url might not be defined.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1119,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 1119,
       'nodeType' => 'PhpParser\\Node\\Expr\\Variable',
       'identifier' => 'variable.undefined',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function current_user_can not found.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1200,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 1200,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'type\' on array{type: \'updated\', message: \'Global fields saved.\'} on left side of ?? always exists and is not nullable.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1328,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 1328,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function current_user_can not found.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1736,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 1736,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'type\' on array{type: \'error\'|\'updated\'|\'warning\', message: non-falsy-string} on left side of ?? always exists and is not nullable.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 1934,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 1934,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'button_group\'|\'checkbox\'|\'email\'|\'file\'|\'image\'|\'number\'|\'password\'|\'radio\'|\'range\'|\'select\'|\'text\'|\'textarea\'|\'true_false\'|\'url\'|\'wysiwyg\' on array{text: \'Text\', textarea: \'Textarea\', number: \'Number\', email: \'Email\', url: \'URL\', range: \'Range\', password: \'Password\', image: \'Image\', ...} on left side of ?? always exists and is not nullable.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 2066,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 2066,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'button_group\'|\'checkbox\'|\'email\'|\'file\'|\'image\'|\'number\'|\'password\'|\'radio\'|\'range\'|\'select\'|\'text\'|\'textarea\'|\'true_false\'|\'url\'|\'wysiwyg\' on array{text: \'Text\', textarea: \'Textarea\', number: \'Number\', email: \'Email\', url: \'URL\', range: \'Range\', password: \'Password\', image: \'Image\', ...} on left side of ?? always exists and is not nullable.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'line' => 2148,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 2148,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
  ),
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function sanitize_textarea_field not found.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php',
       'line' => 205,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 205,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function current_user_can not found.',
       'file' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php',
       'line' => 619,
       'canBeIgnored' => true,
       'filePath' => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 619,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
); },
	'locallyIgnoredErrorsCallback' => static function (): array { return array (
); },
	'linesToIgnore' => array (
),
	'unmatchedLineIgnores' => array (
),
	'collectedDataCallback' => static function (): array { return array (
); },
	'dependencies' => array (
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\forge-fields.php' => 
  array (
    'fileHash' => '7357d594bbd6b5ece6c983cc7f0a967cf62d94e9',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php' => 
  array (
    'fileHash' => 'cce85be311a2750e6fae18ab644f72b9f7ad5e88',
    'dependentFiles' => 
    array (
      0 => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php',
    ),
  ),
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-ui.php' => 
  array (
    'fileHash' => 'a8138e6875009cb3042864f213db19c554e293e1',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php' => 
  array (
    'fileHash' => '0588217be9efd40461c9649f85636f953845c317',
    'dependentFiles' => 
    array (
      0 => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\forge-fields.php',
      1 => 'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php',
    ),
  ),
),
	'exportedNodesCallback' => static function (): array { return array (
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-page.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_get_all_groups',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Helper: get all groups from the option, always as an array,
 * and normalise some defaults (including status).
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_parse_choices_string',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_normalize_choices_string',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Normalize a choices textarea into a canonical format.
 *
 * Returns:
 * [
 *   \'normalized\' => string,
 *   \'errors\'     => array,
 *   \'warnings\'   => array,
 * ]
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_admin_brandbar',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Forge Fields admin brand bar (ACF-style).
 *
 * @param string $subtitle  e.g. \'Field Groups\', \'Edit Field Group\', \'Global Fields\'
 * @param string $add_url   optional CTA url (e.g. Add New). Pass \'\' to hide.
 * @param string $add_label CTA label (defaults to \'Add New\')
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'subtitle',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'add_url',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'add_label',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_admin_subbar',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Forge Fields secondary page bar (under the brand bar).
 *
 * @param string $title
 * @param string $cta_url
 * @param string $cta_label
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'title',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'cta_url',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'cta_label',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'right_html',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_save_all_groups',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Helper: save all groups back to the option.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'groups',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_field_groups_list',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * LIST SCREEN
 * -------------------------------------------------------------------------
 * Shows all field groups with All / Active / Trash filters.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_global_options_page',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * GLOBAL OPTIONS SCREEN
 * -------------------------------------------------------------------------
 * Renders all field groups where location === \'global\' and status !== \'trash\'.
 * Values are stored in a single option: ff_global_fields ( [field_name => value] ).
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_normalize_choices_textarea',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Normalize a choices textarea into a canonical stored string.
 *
 * Option A behavior:
 * - "value" stays "value" (one token line)
 * - "value|Label" becomes "value : Label"
 * - "value : Label" stays "value : Label"
 *
 * Returns: [ $normalized_string, $warnings_array, $errors_array ]
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw_text',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_field_group_edit',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * EDIT / ADD-NEW SCREEN
 * -------------------------------------------------------------------------
 * - If ?group=<id> is present and found: edit that group.
 * - Otherwise: "Add New" (blank group).
 * - Title is REQUIRED.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\admin-ui.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_status_pill',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/** Status pill HTML (active|inactive|trash) */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => 'string',
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'status',
           'type' => 'string',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_count_chip',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/** Numeric “chip” (e.g., fields count) */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => 'string',
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'count',
           'type' => 'int',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'title',
           'type' => 'string',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\Users\\vince\\Desktop\\Projects\\hire-prod\\hire-niagara\\public\\blog\\wp-content\\plugins\\forge-fields\\includes\\core.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_get_field_types',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * -------------------------------------------------------------------------
 * FIELD TYPE REGISTRY (first step of abstraction)
 * -------------------------------------------------------------------------
 *
 * Each field type declares:
 *   - \'render\'   => callable( $field, $value, $post )
 *   - \'sanitize\' => callable( $raw_value, $field, $post_id )
 *
 * You can filter this via `ff_field_types` later to add custom types.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_type_text',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * -------------------------------------------------------------------------
 * FIELD TYPE RENDERERS
 * -------------------------------------------------------------------------
 * These only render the <input>/<textarea> itself.
 * The <tr>, <th>, label etc. stay in ff_render_field_group_metabox().
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'value',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => 'WP_Post',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_type_textarea',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'value',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => 'WP_Post',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_type_number',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'value',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => 'WP_Post',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_type_email',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'value',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => 'WP_Post',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_type_url',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'value',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => 'WP_Post',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_type_range',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'value',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => 'WP_Post',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_type_password',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'value',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => 'WP_Post',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_sanitize_type_text',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * -------------------------------------------------------------------------
 * FIELD TYPE SANITIZERS
 * -------------------------------------------------------------------------
 * Tiny wrappers for now, but this is where per-type rules live.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_sanitize_type_textarea',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_sanitize_type_number',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_sanitize_type_email',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_sanitize_type_url',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_sanitize_type_range',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_sanitize_type_password',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'field',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_register_field_group',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Register a field group at runtime.
 *
 * Called from forge-fields.php on init after loading ff_field_groups.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'group',
           'type' => 'array',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_render_field_group_metabox',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Render fields for a given Forge Fields meta box.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'post',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'box',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_get_field',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Front-end helper:
 *
 *   echo ff_get_field( \'hero_heading\' );        // current post
 *   echo ff_get_field( \'hero_heading\', 123 );   // explicit post ID
 *   echo ff_get_field( \'hero_heading\', \'global\' ); // global/options
 *   echo ff_get_field( \'hero_heading\', \'option\' ); // alias for global
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'name',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'context',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_get_global',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Get a global (site-wide) Forge Field value.
 *
 * Usage:
 *   echo ff_get_global( \'meta_title\' );
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'name',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'default',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ff_parse_choices_string',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'raw',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
); },
];
