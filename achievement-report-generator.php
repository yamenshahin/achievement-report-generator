<?php
/**
 * Plugin Name: Achievement Report Generator
 * Description: A live Arabic RTL achievement-report form with an A4 print layout. Reports are generated in the browser and are not saved.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Site Team
 * Text Domain: achievement-report-generator
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Achievement_Report_Generator {
    const VERSION = '0.1.0';

    public function __construct() {
        add_shortcode('achievement_report_generator', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_assets() {
        $base_url = plugin_dir_url(__FILE__);
        $base_path = plugin_dir_path(__FILE__);

        wp_register_style(
            'achievement-report-generator',
            $base_url . 'assets/report-generator.css',
            [],
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

    private function field($name, $label, $value = '', $type = 'text', $placeholder = '') {
        ob_start(); ?>
        <label class="arg-field">
            <span class="arg-field__label"><?php echo esc_html($label); ?></span>
            <input
                type="<?php echo esc_attr($type); ?>"
                name="<?php echo esc_attr($name); ?>"
                data-report-field="<?php echo esc_attr($name); ?>"
                value="<?php echo esc_attr($value); ?>"
                placeholder="<?php echo esc_attr($placeholder); ?>"
                autocomplete="off"
            >
        </label>
        <?php return ob_get_clean();
    }

    private function metric_fields($index, $title, $description, $value, $with_description = true) {
        ob_start(); ?>
        <fieldset class="arg-metric-fields">
            <legend>بطاقة <?php echo esc_html((string) $index); ?></legend>
            <?php echo $this->field('metric_' . $index . '_title', 'عنوان البطاقة', $title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if ($with_description) : ?>
                <?php echo $this->field('metric_' . $index . '_description', 'وصف مختصر', $description); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
            <?php echo $this->field('metric_' . $index . '_value', 'القيمة', $value); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </fieldset>
        <?php return ob_get_clean();
    }

    public function render_shortcode() {
        wp_enqueue_style('achievement-report-generator');
        wp_enqueue_script('achievement-report-generator');

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
                        <?php echo $this->field('month', 'الشهر', '', 'text', 'مثال: ربيع الآخر'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->field('hijri_year', 'السنة الهجرية', '١٤٤٨'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->field('period_note', 'الفترة / الربع (اختياري)', ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->field('region', 'المنطقة أو الفرع', 'الباحة'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>

                    <h3>أولاً: الخدمات الإفتائية</h3>
                    <div class="arg-metric-form-grid">
                        <?php echo $this->metric_fields(1, 'حالة', 'معالجة حالات الطلاق', '٧'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(2, 'مستفيداً', 'من الإحالات', '٤٤'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(3, 'مراجعاً', 'من الإجابة عن أسئلة المستفيدين حضورياً', '٩٦'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>

                    <h3>ثانياً: البحث العلمي والإصدارات</h3>
                    <div class="arg-metric-form-grid arg-metric-form-grid--two">
                        <?php echo $this->metric_fields(4, 'إعداد البحوث العلمية', '', 'لا يوجد', false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(5, 'إهداء إصدارات الرئاسة', '', '٤٣', false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>

                    <h3>ثالثاً: البرامج والأنشطة العلمية</h3>
                    <div class="arg-metric-form-grid arg-metric-form-grid--two">
                        <?php echo $this->metric_fields(6, 'البرامج العلمية', '', 'لا يوجد', false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php echo $this->metric_fields(7, 'المستفيدون من البرامج العلمية', '', 'لا يوجد', false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>

                    <div class="arg-form-actions">
                        <button type="button" class="arg-button arg-button--primary" data-arg-print>طباعة التقرير</button>
                        <button type="button" class="arg-button arg-button--secondary" data-arg-reset>إعادة القيم الافتراضية</button>
                    </div>
                    <p class="arg-form-note">ملاحظة: القيم الافتراضية الموضوعة هنا للتجربة ومبنية على قراءة الصورة المرجعية؛ يرجى مراجعة المسميات والأرقام قبل اعتماد التقرير.</p>
                </section>

                <section class="arg-preview-panel" aria-label="معاينة التقرير">
                    <div class="arg-preview-toolbar"><strong>معاينة الطباعة</strong><span>A4 · عمودي</span></div>
                    <article class="arg-report" id="arg-print-report" dir="rtl" aria-label="تقرير الإنجازات">
                        <header class="arg-report__header">
                            <div class="arg-report__government">المملكة العربية السعودية</div>
                            <div class="arg-report__organization">الرئاسة العامة للبحوث العلمية والإفتاء</div>
                            <div class="arg-report__seal">إفتاء</div>
                            <h1>تقرير إنجازات</h1>
                            <p class="arg-report__subtitle">فرع الرئاسة العامة للبحوث العلمية والإفتاء</p>
                            <div class="arg-report__period">
                                <span>خلال شهر <b data-report-output="month">—</b></span>
                                <span>لعام <b data-report-output="hijri_year">١٤٤٨</b> هـ</span>
                            </div>
                            <div class="arg-report__period-note" data-report-output="period_note" data-empty-hide>—</div>
                        </header>

                        <section class="arg-report__section">
                            <h2>أولاً: الخدمات الإفتائية</h2>
                            <div class="arg-report__cards arg-report__cards--three">
                                <?php echo $this->report_card(1, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(2, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(3, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </section>

                        <section class="arg-report__section">
                            <h2>ثانياً: البحث العلمي والإصدارات</h2>
                            <div class="arg-report__cards arg-report__cards--two">
                                <?php echo $this->report_card(4, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(5, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </section>

                        <section class="arg-report__section">
                            <h2>ثالثاً: البرامج والأنشطة العلمية</h2>
                            <div class="arg-report__cards arg-report__cards--two">
                                <?php echo $this->report_card(6, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <?php echo $this->report_card(7, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </section>

                        <div class="arg-report__region">
                            <span>فرع الرئاسة العامة للبحوث العلمية والإفتاء بمنطقة</span>
                            <strong data-report-output="region">الباحة</strong>
                        </div>

                        <footer class="arg-report__footer">
                            <div class="arg-report__contact" dir="ltr">
                                <span>011 - 459 5555</span>
                                <span>alifta@alifta.gov.sa</span>
                                <span>alifta.gov.sa&nbsp; | &nbsp;alifta.sa</span>
                            </div>
                            <div class="arg-report__footer-org">الرئاسة العامة للبحوث العلمية والإفتاء</div>
                        </footer>
                    </article>
                </section>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function report_card($index, $with_description) {
        ob_start(); ?>
        <div class="arg-report__card">
            <h3 data-report-output="metric_<?php echo esc_attr($index); ?>_title">—</h3>
            <?php if ($with_description) : ?>
                <p data-report-output="metric_<?php echo esc_attr($index); ?>_description">—</p>
            <?php endif; ?>
            <div class="arg-report__value"><span data-report-output="metric_<?php echo esc_attr($index); ?>_value">—</span></div>
        </div>
        <?php return ob_get_clean();
    }
}

new Achievement_Report_Generator();
