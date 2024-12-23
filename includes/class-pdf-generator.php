<?php
namespace WeeklyPostNewsletter;

if (!defined('ABSPATH')) {
    exit;
}

class PDFGenerator {
    private $pdf;

    public function __construct() {
        // Include TCPDF library
        require_once(ABSPATH . 'wp-includes/class-wp-locale.php');
        require_once(ABSPATH . 'wp-includes/class-phpmailer.php');
        require_once(ABSPATH . 'wp-includes/class-smtp.php');
    }

    public function generate($posts, $title) {
        // Create PDF
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Weekly Newsletter');
        $pdf->SetTitle($title);

        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Create HTML content
        $html = '<h1>' . esc_html($title) . '</h1>';
        $html .= '<h3>Newsletter Date: ' . date('F j, Y') . '</h3>';

        foreach ($posts as $post) {
            $html .= '<h2>' . esc_html(get_the_title($post)) . '</h2>';
            $html .= '<p>' . esc_html(get_the_excerpt($post)) . '</p>';
            $html .= '<hr>';
        }

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html, true, false, true, false, '');

        // Create upload directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $newsletter_dir = $upload_dir['basedir'] . '/newsletters';
        
        if (!file_exists($newsletter_dir)) {
            wp_mkdir_p($newsletter_dir);
        }

        // Save PDF
        $filename = 'newsletter-' . date('Y-m-d-His') . '.pdf';
        $filepath = $newsletter_dir . '/' . $filename;
        $pdf->Output($filepath, 'F');

        return array(
            'filename' => $filename,
            'url' => $upload_dir['baseurl'] . '/newsletters/' . $filename
        );
    }
} 