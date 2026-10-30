<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

http_response_code(404);
azalea_header([
    'title' => 'Page not found | Azalea Yard',
    'description' => 'That page is not on the Azalea Yard site.',
    'path' => '/404.php',
    'robots' => 'noindex,follow',
    'active' => '',
]);
?>
<header class="page-head">
  <h1>That page is not here.</h1>
  <p class="lede">The address does not match a page on this site.</p>
  <p><a class="btn btn-primary" href="/">Go to the home page</a></p>
</header>
<?php azalea_footer(); ?>
