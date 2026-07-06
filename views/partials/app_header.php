<?php
declare(strict_types=1);
/** @var string $page_title */
/** @var string $nav_active galeria|modelos|criar|config|gerenciar_modelos */
$nav_active = $nav_active ?? 'galeria';
?>
<header class="app-header">
  <div class="app-container app-header-inner">
    <div class="app-header-left">
      <?php
      $logo_header = marketing_logo_header_relativo();
      $logo_src = marketing_brand_asset_url($logo_header);
      $logo_is_svg = str_ends_with(strtolower($logo_header), '.svg');
      ?>
        <a href="index.php" class="app-logo-link" aria-label="Central de Marketing Eletropasso — inicio">
          <img
            class="logo<?= $logo_is_svg ? ' logo--central-marketing' : '' ?>"
            src="<?= htmlspecialchars($logo_src, ENT_QUOTES, 'UTF-8') ?>"
            alt="Central de Marketing Eletropasso"
            width="340"
            height="80"
          >
        </a>
    </div>
    <nav class="app-nav" aria-label="Navegacao principal">
      <a href="index.php" class="nav-pill<?= $nav_active === 'galeria' ? ' active' : '' ?>">
        <span class="nav-icon" aria-hidden="true">&#9638;</span> Galeria
      </a>
      <?php if ($nav_active !== 'galeria'): ?>
      <a href="modelos.php" class="nav-pill nav-pill--primary<?= in_array($nav_active, ['modelos', 'criar'], true) ? ' active' : '' ?>">
        <span class="nav-icon" aria-hidden="true">+</span> Novo Encarte
      </a>
      <?php endif; ?>
      <a href="config.php" class="nav-pill<?= $nav_active === 'config' ? ' active' : '' ?>">
        <span class="nav-icon" aria-hidden="true">&#9881;</span> Configuracoes
      </a>
      <a href="gerenciar-modelos.php" class="nav-pill<?= $nav_active === 'gerenciar_modelos' ? ' active' : '' ?>">
        <span class="nav-icon" aria-hidden="true">&#9635;</span> Modelos
      </a>
    </nav>
  </div>
</header>
