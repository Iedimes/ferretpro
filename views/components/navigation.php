<?php
/**
 * Componente de navegación para volver a página anterior o al dashboard
 * 
 * @param string $backPage URL de la página anterior (ej: '?page=sales')
 * @param string $label Texto del botón (ej: 'Volver a Ventas')
 * @param bool $showDashboard Si true, muestra también botón al dashboard
 */
function renderBackButton($backPage = '?page=dashboard', $label = 'Volver', $showDashboard = true) {
    $html = '<div class="mb-3">';
    $html .= '<a href="' . htmlspecialchars($backPage) . '" class="btn btn-nav-back me-2">';
    $html .= '<i class="bi bi-arrow-left"></i> ' . htmlspecialchars($label);
    $html .= '</a>';
    
    if ($showDashboard && strpos($backPage, 'dashboard') === false) {
        $html .= '<a href="?page=dashboard" class="btn btn-outline-primary">';
        $html .= '<i class="bi bi-speedometer2"></i> Dashboard';
        $html .= '</a>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Componente de header con acciones
 */
function renderSectionHeader($title, $icon = '', $actions = '') {
    $html = '<div class="d-flex justify-content-between align-items-center mb-4">';
    $html .= '<h4 class="mb-0">';
    if ($icon) $html .= '<i class="bi ' . htmlspecialchars($icon) . ' me-2"></i>';
    $html .= htmlspecialchars($title);
    $html .= '</h4>';
    if ($actions) $html .= $actions;
    $html .= '</div>';
    return $html;
}
