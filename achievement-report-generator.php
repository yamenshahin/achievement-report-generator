<?php
/**
 * Plugin Name: Achievement Report Generator
 * Description: A live Arabic RTL achievement-report form with an A4 print layout. Reports are generated in the browser and are not saved.
 * Version: 0.7.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Yamen Shahin <yamenshahin@gmail.com>
 * Author URI: https://github.com/yamenshahin
 * Plugin URI: https://github.com/yamenshahin
 * Text Domain: achievement-report-generator
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Achievement_Report_Generator
{
    const VERSION = '0.7.0';
    const TARGET_PAGE_ID = 13630;
    const STAGING_PAGE_ID = 174664;

    public function __construct()
    {
        add_shortcode('achievement_report_generator', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);

        add_action('wp', [$this, 'isolate_target_page']);
        add_action('wp_enqueue_scripts', [$this, 'dequeue_unwanted_styles'], 9999);
    }

    public function register_assets()
    {
        $base_url = plugin_dir_url(__FILE__);
        $base_path = plugin_dir_path(__FILE__);

        wp_register_style(
            'achievement-report-generator-fonts',
            'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Aref+Ruqaa:wght@700&display=swap',
            [],
            null
        );

        wp_register_style(
            'achievement-report-generator',
            $base_url . 'assets/report-generator.css',
            ['achievement-report-generator-fonts'],
            file_exists($base_path . 'assets/report-generator.css') ? filemtime($base_path . 'assets/report-generator.css') : self::VERSION
        );

        wp_register_script(
            'achievement-report-generator',
            $base_url . 'assets/report-generator.js',
            [],
            file_exists($base_path . 'assets/report-generator.js') ? filemtime($base_path . 'assets/report-generator.js') : self::VERSION,
            true
        );
    }

    private function target_page_ids()
    {
        $ids = [self::TARGET_PAGE_ID];

        // The staging/local copy of the report page has a different ID.
        if (wp_get_environment_type() !== 'production') {
            $ids[] = self::STAGING_PAGE_ID;
        }

        return $ids;
    }

    public function isolate_target_page()
    {
        if (is_page($this->target_page_ids())) {
            remove_all_actions('get_header');
            remove_all_actions('get_footer');

            add_filter('template_include', function ($template) {
                $plugin_template = plugin_dir_path(__FILE__) . 'isolated-page-template.php';
                if (file_exists($plugin_template)) {
                    return $plugin_template;
                }
                return $template;
            }, 9999);
        }
    }

    public function dequeue_unwanted_styles()
    {
        if (is_page($this->target_page_ids())) {
            global $wp_styles;
            if (!empty($wp_styles->registered)) {
                foreach ($wp_styles->registered as $handle => $style) {
                    if (!in_array($handle, ['achievement-report-generator', 'achievement-report-generator-fonts'], true)) {
                        wp_deregister_style($handle);
                        wp_dequeue_style($handle);
                    }
                }
            }
        }
    }

    private function field($name, $label, $value = '', $type = 'text', $placeholder = '')
    {
        ob_start(); ?>
        <label class="arg-field">
            <span class="arg-field__label"><?php echo esc_html($label); ?></span>
            <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>"
                data-report-field="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"
                placeholder="<?php echo esc_attr($placeholder); ?>" autocomplete="off">
        </label>
        <?php return ob_get_clean();
    }

    private function metric_fields($index, $title, $description, $value, $with_description = true, $unit = null)
    {
        ob_start(); ?>
        <fieldset class="arg-metric-fields">
            <legend>بطاقة <?php echo esc_html((string) $index); ?></legend>
            <?php echo $this->field('metric_' . $index . '_title', 'عنوان البطاقة', $title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if ($with_description): ?>
                <?php echo $this->field('metric_' . $index . '_description', 'وصف مختصر', $description); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
            <?php echo $this->field('metric_' . $index . '_value', 'القيمة', $value); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if ($unit !== null): ?>
                <?php echo $this->field('metric_' . $index . '_unit', 'وحدة القياس', $unit); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </fieldset>
        <?php return ob_get_clean();
    }

    public function render_shortcode()
    {
        wp_enqueue_style('achievement-report-generator');
        wp_enqueue_script('achievement-report-generator');

        $theme_logo_path = get_theme_file_path('/assets/images/logo.png');
        $theme_logo_url = get_theme_file_uri('/assets/images/logo.png');
        $plugin_logo_path = plugin_dir_path(__FILE__) . 'assets/images/logo.png';
        $plugin_logo_url = plugin_dir_url(__FILE__) . 'assets/images/logo.png';

        if (file_exists($theme_logo_path)) {
            $logo_url = $theme_logo_url;
        } elseif (file_exists($plugin_logo_path)) {
            $logo_url = $plugin_logo_url;
        } else {
            $logo_url = '';
        }

        ob_start(); ?>
        <div class="arg-app" dir="rtl">
            <div class="arg-page-heading">
                <h2>إنشاء تقرير الإنجازات</h2>
                <p>أدخل البيانات في النموذج، ثم راجع المعاينة واطبع التقرير. لا يتم حفظ البيانات في WordPress.</p>
            </div>
            <div class="arg-workspace">
                <section class="arg-form-panel" aria-label="نموذج بيانات التقرير">
                    <h3>بيانات التقرير</h3>
                    <div class="arg-form-grid">
                        <?php echo $this->field('month', 'الشهر الهجري', '( 3 ) ربيع الأول', 'text', 'مثال: ( 3 ) ربيع الأول'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->field('hijri_year', 'السنة الهجرية', '1448'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->field('region', 'المنطقة أو الفرع', 'عسير'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <h3>أولاً: الخدمات الإفتائية</h3>
                    <div class="arg-metric-form-grid">
                        <?php echo $this->metric_fields(1, 'حالة', 'معالجة حالات الطلاق', '20'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(2, 'مستفيداً', 'من الإجابة عن أسئلة المستفيدين الهاتفية', '858'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(3, 'مراجعاً', 'من الإجابة عن أسئلة المستفيدين الحضورية', '151'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <h3>ثانياً: البحث العلمي والإصدارات</h3>
                    <div class="arg-metric-form-grid arg-metric-form-grid--two">
                        <?php echo $this->metric_fields(4, 'إعداد البحوث العلمية', '', '29', false, 'بحثاً'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(5, 'إهداء إصدارات الرئاسة', '', '3010', false, 'كتاباً'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <h3>ثالثاً: البرامج والأنشطة العلمية</h3>
                    <div class="arg-metric-form-grid arg-metric-form-grid--two">
                        <?php echo $this->metric_fields(6, 'البرامج العلمية', '', '6', false, 'برامج'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(7, 'المستفيدون من البرامج العلمية', '', '570', false, 'مستفيداً'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <div class="arg-form-actions">
                        <button type="button" class="arg-button arg-button--primary" data-arg-print>طباعة التقرير</button>
                        <button type="button" class="arg-button arg-button--secondary" data-arg-reset>إعادة القيم
                            الافتراضية</button>
                    </div>
                    <p class="arg-form-note">ملاحظة: القيم الافتراضية الموضوعة هنا للتجربة ومبنية على قراءة الصورة المرجعية؛
                        يرجى مراجعة المسميات والأرقام قبل اعتماد التقرير.</p>
                </section>
                <section class="arg-preview-panel" aria-label="معاينة التقرير">
                    <div class="arg-preview-toolbar"><strong>معاينة الطباعة</strong><span>A4 · عمودي</span></div>
                    <article class="arg-report" id="arg-print-report" dir="rtl" aria-label="تقرير الإنجازات">
                        <header class="arg-report__header">
                            <?php if ($logo_url !== ''): ?>
                                <div class="arg-report__logo-wrap">
                                    <img class="arg-report__logo" src="<?php echo esc_url($logo_url); ?>"
                                        alt="شعار الرئاسة العامة للبحوث العلمية والإفتاء" width="540" height="172">
                                </div>
                            <?php endif; ?>
                            <h1>تقريـــر إنجـــازات</h1>
                            <p class="arg-report__subtitle">فرع الرئاسة العامة للبحوث العلمية والإفتاء</p>
                            <div class="arg-report__period">
                                خلال شهر <span data-report-output="month">( 3 ) ربيع الأول</span>
                                لعام <span data-report-output="hijri_year">1448</span> هـ
                            </div>
                        </header>
                        <section class="arg-report__section">
                            <h2 class="arg-report__tab"><span>أولاً: الخدمات الإفتائية</span></h2>
                            <div class="arg-report__panel arg-report__cards arg-report__cards--three">
                                <?php echo $this->report_card(1, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(2, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(3, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </section>
                        <section class="arg-report__section">
                            <h2 class="arg-report__tab"><span>ثانياً: البحث العلمي والإصدارات</span></h2>
                            <div class="arg-report__panel arg-report__cards arg-report__cards--two">
                                <?php echo $this->report_card(4, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(5, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </section>
                        <section class="arg-report__section">
                            <h2 class="arg-report__tab"><span>ثالثاً: البرامج والأنشطة العلمية</span></h2>
                            <div class="arg-report__panel arg-report__cards arg-report__cards--two">
                                <?php echo $this->report_card(6, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(7, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </section>
                        <div class="arg-report__closing">
                            <p class="arg-report__regards">مـع تحيـات</p>
                            <p class="arg-report__region">فرع الرئاسة العامة للبحوث العلمية والإفتاء بمنطقة
                                <span data-report-output="region">عسير</span></p>
                        </div>
                        <div class="arg-report__footer" dir="ltr" aria-label="بيانات التواصل">
                            <ul class="arg-report__contact">
                                <li><?php echo $this->icon('phone'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>011 - 459 5555</li>
                                <li><?php echo $this->icon('mail'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>alifta@alifta.gov.sa</li>
                                <li><?php echo $this->icon('web'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>alifta.gov.sa</li>
                                <li><?php echo $this->icon('x'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>alifta.sa</li>
                            </ul>
                            <div class="arg-report__footer-org" dir="rtl">الرئاسة العامة للبحوث العلمية والإفتاء</div>
                        </div>
                    </article>
                </section>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function report_card($index, $with_description)
    {
        ob_start(); ?>
        <div class="arg-report__card">
            <?php if ($with_description): ?>
                <div class="arg-report__value"><span data-report-output="metric_<?php echo esc_attr($index); ?>_value">—</span></div>
                <h3 data-report-output="metric_<?php echo esc_attr($index); ?>_title">—</h3>
                <p data-report-output="metric_<?php echo esc_attr($index); ?>_description">—</p>
            <?php else: ?>
                <h3 data-report-output="metric_<?php echo esc_attr($index); ?>_title">—</h3>
                <div class="arg-report__value arg-report__value--pill">
                    <span data-report-output="metric_<?php echo esc_attr($index); ?>_value">—</span>
                    <small class="arg-report__unit" data-report-output="metric_<?php echo esc_attr($index); ?>_unit" data-empty-hide>—</small>
                </div>
            <?php endif; ?>
        </div>
        <?php return ob_get_clean();
    }

    private function icon($name)
    {
        $paths = [
            'phone' => '<path d="M6.6 10.8a15.2 15.2 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/>',
            'mail' => '<path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm1 2.3V17h16V7.3l-8 5.2z"/><path d="M4.5 7h15L12 11.9z"/>',
            'web' => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.9 6h-3a15.7 15.7 0 0 0-1.4-3.6A8 8 0 0 1 18.9 8zM12 4c.8 1.2 1.5 2.5 1.9 4h-3.8c.4-1.5 1.1-2.8 1.9-4zM4.3 14a8.2 8.2 0 0 1 0-4h3.4a16.5 16.5 0 0 0 0 4zm.8 2h3a15.7 15.7 0 0 0 1.4 3.6A8 8 0 0 1 5.1 16zm3-8h-3a8 8 0 0 1 4.4-3.6A15.7 15.7 0 0 0 8.1 8zM12 20c-.8-1.2-1.5-2.5-1.9-4h3.8c-.4 1.5-1.1 2.8-1.9 4zm2.3-6H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4zm.2 5.6a15.7 15.7 0 0 0 1.4-3.6h3a8 8 0 0 1-4.4 3.6zm1.8-5.6a16.5 16.5 0 0 0 0-4h3.4a8.2 8.2 0 0 1 0 4z"/>',
            'x' => '<path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.3L5.3 21H2.2l7.2-8.3L2 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/>',
        ];

        return '<svg class="arg-report__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
    }
}

new Achievement_Report_Generator();