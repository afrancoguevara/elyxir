<?php
namespace Elyxir;

defined('ABSPATH') || exit;

// Estilos sólo para editor (si quieres algo específico distinto a modern.css)
function enqueue_block_editor_assets() {
  // Ejemplo: un CSS para mejorar contraste en Gutenberg.
  // wp_enqueue_style('elyxir-editor', URI . '/assets/css/editor.css', [], versioned('/assets/css/editor.css'));
}
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets');
