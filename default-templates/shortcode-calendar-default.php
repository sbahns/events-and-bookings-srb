<section class="wpmudevevents-list">
<?php
if (!class_exists('Eab_CalendarTable_EventShortcodeCalendar')) require_once EAB_PLUGIN_DIR . 'lib/class_eab_calendar_helper.php';
$renderer = new Eab_CalendarTable_EventShortcodeCalendar($events);

$renderer->set_class($args['class']);
$renderer->set_footer($args['footer']);
$renderer->set_scripts(!$args['override_scripts']);
echo $renderer->get_month_calendar($args['date']);
?>
</section>