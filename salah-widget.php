<?php

/*
Plugin Name: Salah Widget
Plugin URI: https://github.com/Delilovic/salah-widget
Description: Adds a custom salah widget used for Muslim prayer times and Ramadan fasting.
Version: 1.1
Author: ndelilovic
Author URI: https://github.com/Delilovic
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

class Salah_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'salah_widget',
            'Salah Widget',
            array(
                'customize_selective_refresh' => true,
            )
        );
    }

    public function form($instance)
    {
        $timezone = !empty(get_option('timezone_string')) ? get_option('timezone_string') : 'Europe/Vienna';

        $defaults = array(
            'salah_city' => $timezone,
            'select_theme' => 'Classic',
            'select_language' => 'English'
        );

        $date_time = new DateTime();
        $date_time->setTimezone(new DateTimeZone($timezone));

        $current_date = $date_time->format('d') . '.' . $date_time->format('m') . '.' . $date_time->format('Y');
        $current_time = $date_time->format('H') . ':' . $date_time->format('i') . ':' . $date_time->format('s');
        $display_date_time = '<br><b>' . $current_date . ' - ' . $current_time . '</b><br>' . '(' . __('Please ensure that the displayed date and time are same with your local/city date and time, if not change the Timezone in your WordPress settings') . ' !)';

        ?>
        <?= $display_date_time ?><br><br>
        <?php

        ?>
        <label for='<?php echo $this->get_field_id('salah_city'); ?>'><?php _e('Add');
            echo ' <b>salah.csv</b> ';
            _e('document');
            echo ':<br>'; ?></label>


        <?php
        wp_enqueue_media();
        media_buttons();

        extract(wp_parse_args(( array )$instance, $defaults));

        ?>

        <p>
            <label for='<?php echo $this->get_field_id('salah_city'); ?>'><?php _e('Enter Location');
                echo ':'; ?></label>
            <label for='<?php echo esc_attr($this->get_field_id('salah_city')); ?>'></label>
            <input class='widefat' id='<?php echo esc_attr($this->get_field_id('salah_city')); ?>'
                   name='<?php echo esc_attr($this->get_field_name('salah_city')); ?>' type='text'
                   value='<?php echo esc_attr($salah_city); ?>'/>
        </p>

        <p>
            <label for='<?php echo $this->get_field_id('select_theme'); ?>'><?php _e('Select Theme');
                echo ':'; ?></label>
            <select name='<?php echo $this->get_field_name('select_theme'); ?>'
                    id='<?php echo $this->get_field_id('select_theme'); ?>' class="widefat">
                <?php
                // Your options array
                $options = array(
                    'classicTable' => __('Classic'),
                    'greenTable' => __('Green'),
                    'blueTable' => __('Blue'),
                    'redTable' => __('Red'),
                    'greyTable' => __('Grey')
                );

                // Loop through options and add each one to the select dropdown
                foreach ($options as $key => $name) {
                    echo '<option value="' . esc_attr($key) . '" id="' . esc_attr($key) . '" ' . selected($select_theme, $key, false) . '>' . $name . '</option>';

                } ?>
            </select>
        </p>

        <p>
            <label for='<?php echo $this->get_field_id('select_language'); ?>'><?php _e('Select Frontend Language');
                echo ':'; ?></label>
            <select name='<?php echo $this->get_field_name('select_language'); ?>'
                    id='<?php echo $this->get_field_id('select_language'); ?>' class="widefat">
                <?php
                // Your options array
                $options = array(
                    'en_language' => __('English'),
                    'de_language' => __('German'),
                    'bs_language' => __('Bosnian')
                );

                // Loop through options and add each one to the select dropdown
                foreach ($options as $key => $name) {
                    echo '<option value="' . esc_attr($key) . '" id="' . esc_attr($key) . '" ' . selected($select_language, $key, false) . '>' . $name . '</option>';

                } ?>
            </select>
        </p>

        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['salah_city'] = isset($new_instance['salah_city']) ? wp_strip_all_tags($new_instance['salah_city']) : '';
        $instance['select_theme'] = isset($new_instance['select_theme']) ? wp_strip_all_tags($new_instance['select_theme']) : '';
        $instance['select_language'] = isset($new_instance['select_language']) ? wp_strip_all_tags($new_instance['select_language']) : '';
        return $instance;
    }


    public function widget($args, $instance)
    {
        global $en_language;
        global $de_language;
        global $bs_language;

        extract($args);

        $salah_city = !empty($instance['salah_city']) ? apply_filters('widget_title', $instance['salah_city']) : 'Europe/Vienna';
        $salah_theme = !empty($instance['select_theme']) ? $instance['select_theme'] : 'classicTable';
        $salah_language = !empty($instance['select_language']) ? $instance['select_language'] : 'en_language';

        $file_id = get_option('salah');
        $file = get_attached_file($file_id);
        $attachment_title = get_the_title($file_id);

        if ($file && $attachment_title == 'salah') {
            $current_date = date('d') . '/' . date('m') . '/' . date('Y');
            $csv_data = file_get_contents($file);
            $csv_data = iconv('windows-1250', 'utf-8', $csv_data);

            $lines = explode("\n", $csv_data); // split data by new lines
            foreach ($lines as $i => $line) {
                $values = explode(',', $line); // split lines by commas
                if ($values[0] == $current_date) {
                    $date = $values[1];
                    $fajr = $values[2];
                    $sunrise = $values[3];
                    $dhuhr = $values[4];
                    $asr = $values[5];
                    $maghrib = $values[6];
                    $isha = $values[7];
                }
            }
        } else {
            $date = 'Error';
            $fajr = 'Error';
            $sunrise = 'Error';
            $dhuhr = 'Error';
            $asr = 'Error';
            $maghrib = 'Error';
            $isha = 'Error';
        }

        $selected_language = null;

        switch ($salah_language) {
            case 'en_language':
                $selected_language = $en_language;
                break;
            case 'de_language':
                $selected_language = $de_language;
                break;
            case 'bs_language':
                $selected_language = $bs_language;
                break;
        }


        echo $before_widget;
        echo '<table class="' . $salah_theme . '">';
        echo '<thead>';
        echo '<tr>';
        echo '<th colspan="4">' . $selected_language['Salah time for '] . $salah_city . '<br>' . $date . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        echo '<td>' . $selected_language['Fajr'] . '</td>';
        echo '<td>' . $fajr . '</td>';
        echo '<td>' .$selected_language['Asr'] . '</td>';
        echo '<td>' . $asr . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . $selected_language['Sunrise'] . '</td>';
        echo '<td>' . $sunrise . '</td>';
        echo '<td>' . $selected_language['Maghrib'] . '</td>';
        echo '<td >' . $maghrib . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . $selected_language['Dhuhr'] . '</td>';
        echo '<td>' . $dhuhr . '</td>';
        echo '<td>' . $selected_language['Isha'] . '</td>';
        echo '<td>' . $isha . '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo $after_widget;
    }
}

$en_language = [
    'Salah time for ' => 'Salah time for ',
    'Fajr' => 'Fajr',
    'Sunrise' => 'Sunrise',
    'Dhuhr' => 'Dhuhr',
    'Asr' => 'Asr',
    'Maghrib' => 'Maghrib',
    'Isha' => 'Isha'
];

$de_language = [
    'Salah time for ' => 'Gebetszeiten für ',
    'Fajr' => 'Morgengebet',
    'Sunrise' => 'Sonnenaufgang',
    'Dhuhr' => 'Mittagsgebet',
    'Asr' => 'Nachmittagsgebet',
    'Maghrib' => 'Abendgebet',
    'Isha' => 'Nachtgebet'
];

$bs_language = [
    'Salah time for ' => 'Vaktija za ',
    'Fajr' => 'Zora',
    'Sunrise' => 'Izlazak sunca',
    'Dhuhr' => 'Podne',
    'Asr' => 'Ikindija',
    'Maghrib' => 'Akšam',
    'Isha' => 'Jacija'
];

function register_salah_widget()
{
    register_widget('Salah_Widget');
}

function adding_salah_styles()
{
    wp_register_style('salah_stylesheet', plugins_url('/public/css/salah-stylesheet.css', __FILE__));
    wp_enqueue_style('salah_stylesheet');
}

function new_attachment($att_id)
{
    $attachment_title = get_the_title($att_id);
    if ($attachment_title == 'salah') {
        update_option('salah', $att_id);
    }
}

add_action('widgets_init', 'register_salah_widget');
add_action('wp_enqueue_scripts', 'adding_salah_styles');
add_action('add_attachment', 'new_attachment');