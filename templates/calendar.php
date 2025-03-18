<?php
/**
 * Šablona pro vykreslení kalendáře událostí
 *
 * @var array $atts Atributy shortcodu
 */

// Generování unikátního ID pro kalendář
$calendar_id = 'jsm-event-calendar-' . uniqid();

// Aktuální měsíc a rok - zajistíme, že nikdy nezobrazíme minulé měsíce
$current_month = date('m');
$current_year = date('Y');

$month = absint($atts['month']);
$year = absint($atts['year']);

// Kontrola platnosti měsíce a roku
if ($month < 1 || $month > 12) {
    $month = $current_month;
}
if ($year < 1970 || $year > 2100) {
    $year = $current_year;
}

// Zajistíme, že nepůjdeme do minulosti
if ($year < $current_year || ($year == $current_year && $month < $current_month)) {
    $month = $current_month;
    $year = $current_year;
}

// Získání názvů měsíců a dnů v týdnu
$month_name = date_i18n('F', strtotime("$year-$month-01"));
$weekdays = array(
    __('Po', 'jsm-wp-event-calendar'),
    __('Út', 'jsm-wp-event-calendar'),
    __('St', 'jsm-wp-event-calendar'),
    __('Čt', 'jsm-wp-event-calendar'),
    __('Pá', 'jsm-wp-event-calendar'),
    __('So', 'jsm-wp-event-calendar'),
    __('Ne', 'jsm-wp-event-calendar'),
);

// Počet dní v měsíci
$days_in_month = date('t', strtotime("$year-$month-01"));

// OPRAVENO: Získání prvního dne měsíce (0 = pondělí, 6 = neděle - evropský formát)
$first_day_timestamp = strtotime("$year-$month-01");
$first_day_of_week = date('N', $first_day_timestamp); // 1 (pondělí) až 7 (neděle)
$first_day_of_month = $first_day_of_week - 1; // Konverze na 0-6, kde 0 je pondělí

// Dnes
$today = date('Y-m-d');
$today_day = date('j');
$today_month = date('m');
$today_year = date('Y');

// Kategorie pro filtrování
$category = !empty($atts['category']) ? $atts['category'] : '';

// Zobrazit seznam událostí pod kalendářem
$show_list = ($atts['show_list'] === 'yes');
?>

<div id="<?php echo esc_attr($calendar_id); ?>" class="jsm-event-calendar-wrapper" data-month="<?php echo esc_attr($month); ?>" data-year="<?php echo esc_attr($year); ?>" data-show-list="<?php echo esc_attr($atts['show_list']); ?>" data-category="<?php echo esc_attr($category); ?>">
    <!-- Navigace kalendáře -->
    <div class="jsm-event-calendar-nav">
        <h2 id="<?php echo esc_attr($calendar_id); ?>-title" class="jsm-event-calendar-title"><?php echo esc_html($month_name . ' ' . $year); ?></h2>

        <div class="jsm-event-calendar-nav-buttons">
            <?php
            // Zobrazíme tlačítko Předchozí pouze pokud nejsme v aktuálním měsíci
            $show_prev = ($month > $current_month || $year > $current_year);
            if ($show_prev) :
            ?>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-prev" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Předchozí', 'jsm-wp-event-calendar'); ?>
            </button>
            <?php endif; ?>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-today" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Dnes', 'jsm-wp-event-calendar'); ?>
            </button>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-next" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Další', 'jsm-wp-event-calendar'); ?>
            </button>
        </div>
    </div>

    <!-- Kalendář -->
    <div id="<?php echo esc_attr($calendar_id); ?>-table" class="jsm-event-calendar-table-wrapper">
        <table class="jsm-event-calendar-table">
            <thead>
                <tr>
                    <?php foreach ($weekdays as $weekday) : ?>
                        <th><?php echo esc_html($weekday); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    // Prázdné buňky před prvním dnem měsíce
                    for ($i = 0; $i < $first_day_of_month; $i++) {
                        echo '<td class="jsm-event-calendar-day empty other-month"></td>';
                    }

                    // Dny v měsíci
                    $day_count = $first_day_of_month;
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        // Nový řádek po 7 dnech
                        if ($day_count % 7 === 0 && $day_count > 0) {
                            echo '</tr><tr>';
                        }

                        // Datum pro tento den
                        $date = sprintf('%s-%s-%s', $year, str_pad($month, 2, '0', STR_PAD_LEFT), str_pad($day, 2, '0', STR_PAD_LEFT));

                        // Třída pro dnešní den
                        $today_class = '';
                        if ($day == $today_day && $month == $today_month && $year == $today_year) {
                            $today_class = ' today';
                        }

                        echo '<td class="jsm-event-calendar-day' . esc_attr($today_class) . '" data-date="' . esc_attr($date) . '">';
                        echo '<span class="jsm-event-calendar-day-number">' . esc_html($day) . '</span>';

                        // Zde by byly události, ale zobrazíme je dynamicky pomocí JavaScriptu

                        echo '</td>';

                        $day_count++;
                    }

                    // Prázdné buňky na konci měsíce
                    while ($day_count % 7 !== 0) {
                        echo '<td class="jsm-event-calendar-day empty other-month"></td>';
                        $day_count++;
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Seznam událostí -->
    <?php if ($show_list) : ?>
        <div id="<?php echo esc_attr($calendar_id); ?>-list" class="jsm-event-list-wrapper">
            <!-- Seznam událostí bude naplněn dynamicky pomocí JavaScriptu -->
        </div>
    <?php endif; ?>

</div>

<!-- Modální okno pro detail události -->
<div id="jsm-event-modal" class="jsm-event-modal">

    <div id="jsm-event-modal-content" class="jsm-event-modal-content">
        <!-- Obsah modálního okna bude naplněn dynamicky -->

    </div>

</div>