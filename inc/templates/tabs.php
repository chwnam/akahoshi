<?php
/**
 * @var array         $tabs
 * @var callable|null $callback
 */

$sel_tab  = null;
$callback = null;

if (isset($tabs) && !isset($_GET['tab'])) {
    $_GET['tab'] = $tabs[0]['tab'];
}

?>
    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab) : ?>
            <?php
            $tab    = wp_parse_args($tab, [
                    'tab'   => '',
                    'title' => '',
                    'url'   => '#',
            ]);
            $active = $tab['tab'] === ($_GET['tab'] ?? '');
            if ($active && isset($tab['callback']) && is_callable($tab['callback'])) {
                $callback = $tab['callback'];
                $sel_tab  = $tab['tab'];
            }
            ?>

            <?php if ($tab['tab'] && $tab['url'] && $tab['title']): ?>
                <a id="<?php esc_attr($tab['tab']); ?>" href="<?php echo esc_url($tab['url']); ?>" class="nav-tab<?php echo $active ? ' nav-tab-active' : '' ?>"><?php echo esc_html($tab['title']); ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
<?php
if (is_callable($callback) && $sel_tab) {
    call_user_func($callback, $sel_tab);
}
