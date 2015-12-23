<?php
require_once 'FPdfPprotection.php';

error_reporting(E_ALL ^ E_DEPRECATED);
define('FPDF_FONTPATH', '/tmp/font/');
define('CCDATE', date('Y M d H:i'));

define("_SYSTEM_TTFONTS", FPDF_FONTPATH."unifont/");



class AweryPdf extends FPDF_Protection
{

    protected $awery_text = 'Awery Aviation Solutions - www.awery.aero / ';

    protected $printHeaderSign = true;

    protected $signAlert = false;
    protected $prefix = '';

    protected $calculation = false;
    protected $calculatedHeight = 0;

    protected $invoiceNumber = null;
    protected $invoiceDate = null;
    protected $invoiceDueDate = null;

    protected $addInvoiceIdToDate = true;

    protected $reference = null;

    protected $textHeader = null;
    protected $disableFooter = false;

    protected $sign;
    protected $showPageNo = false;

    protected $hideHeader = false;
    protected $hideHeaderImage = false;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        if (!file_exists(FPDF_FONTPATH)) {
            mkdir(FPDF_FONTPATH, 0700, true);
        }
        if (!file_exists(_SYSTEM_TTFONTS)) {
            mkdir(_SYSTEM_TTFONTS, 0700, true);
        }
        parent::__construct($orientation, $unit, $format);
#        parent::tFPDF($orientation,$unit,$format);
        $this->awery_text .= date('Y M d H:i');
        $this->AddFont('ArialUU', '', 'arial.ttf', true);
        $this->AddFont('ArialUU', 'B', 'arialbd.ttf', true);
        $this->AddFont('ArialUU', 'I', 'ariali.ttf', true);
        $this->AddFont('ArialUU', 'BI', 'arialbi.ttf', true);
        $this->SetFont('ArialUU', '', 10);

//        if ( file_exists(FPDF_FONTPATH.'unifont/Arimo-Regular.ttf') )
//            $this->AddFont('Arimo','','Arimo-Regular.ttf',true);
//        if ( file_exists(FPDF_FONTPATH.'unifont/Arimo-Bold.ttf') )
//            $this->AddFont('Arimo','B','Arimo-Bold.ttf',true);
//        if ( file_exists(FPDF_FONTPATH.'unifont/Arimo-Italic.ttf') )
//            $this->AddFont('Arimo','I','Arimo-Italic.ttf',true);
//        if ( file_exists(FPDF_FONTPATH.'unifont/Arimo-BoldItalic.ttf') )
//            $this->AddFont('Arimo','BI','Arimo-BoldItalic.ttf',true);

    }

//    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4') {
//        $this->Open();
//        if ($_SERVER['HTTP_HOST'] == 'demo.awery.com.ua') {
//            $this->AddFont('ArialUnicodeMS','','arialuni.php');
//        }
//        parent::FPDF($orientation, $unit, $format);
//    }

    public function GetCenterPos($string, $maxWidth = null)
    {
        if ($maxWidth == null) {
            $maxWidth = $this->w + $this->lMargin - $this->rMargin;
        }

        $stringWidth = $this->GetStringWidth($string);
        return ($maxWidth - $stringWidth) / 2;
    }

    public function _CenterText($y, $text)
    {
        $this->Text($this->GetCenterPos($text), $y, $text);
    }

    public function CenterText($text, $y = null)
    {
        if (preg_match('~[0-9]+~', $text, $matches) AND is_string($y)) {
            return $this->_CenterText($text, $y);
        }


        if ($y == null) {
            $y = $this->y;
        }

        $this->Text($this->GetCenterPos($text), $y, $text);
    }

    public function SetSubject($subject, $sign = false, $isUTF8 = false)
    {
        parent::SetSubject($subject, $isUTF8);
        $this->setSign($sign);
    }

    public function setSign($sign)
    {
        $this->sign = $sign;
    }

    public function addInvoiceIdToDate($flag = true)
    {
        $this->addInvoiceIdToDate = (bool)$flag;
    }

    public function setAweryText($awery_text)
    {
        $this->awery_text = $awery_text;
    }

    public function printHeaderSign($print = true)
    {
        $this->printHeaderSign = $print;
    }

    public function ShowPageNo($flag = true)
    {
        $this->showPageNo = (bool)$flag;
    }

    public function SetSignAlert($flag = true)
    {
        $this->signAlert = $flag;
    }

    public function SetInvoice($number, $date = null, $id = null, $date_size = 12, $due_date = null)
    {
        $this->invoiceNumber = $number;
        $this->invoiceDate = $date;
        if (!is_null($due_date) && $due_date != '0000-00-00')
            $this->invoiceDueDate = $due_date;
        $this->invoiceId = $id;
        if (is_null($date_size)) $date_size = 12;
        $this->invoiceDateSize = (int)$date_size;
    }

    public function SetResourcePrefix($prefix)
    {
        $this->prefix = strtolower($prefix);
    }

    public function SetReference($ref_no)
    {
        $this->reference = $ref_no;
    }

    public function CurrencyNote($dr_rate, $cr_rate = null, $draw = true, $currency_id = null, $dr_cr_id = null)
    {
        if ($cr_rate === null) {
            $dr_rate = $cr_rate;
        }

        $text = '';
        if ($currency_id == null AND $dr_cr_id === null) {
            if ($dr_rate > 1 AND $cr_rate > 1) {
                if (floatval($dr_rate) == floatval($cr_rate)) {
                    $text = 'Currency rate: ' . $dr_rate;
                } else {
                    $text = 'Dr. currency rate: ' . $dr_rate . ', Cr. currency rate: ' . $cr_rate;
                }
            }
        } elseif ($currency_id !== $dr_cr_id AND floatval($dr_rate) == floatval($cr_rate)) {
            $Acc_Info = new Acc_Info();
            $text = 'Currency rate: 1 ' . $Acc_Info->getCurrencyName($dr_cr_id) . ' = ' . $dr_rate . ' ' . $Acc_Info->getCurrencyName($currency_id);
        }

        if ($draw AND !empty($text)) {
            $this->Cell(0, 5, 'Notes: ' . $text);
        } else {
            return $text;
        }
    }

    public function SetTextHeader($text)
    {
        $this->textHeader = $text;
    }

    public function HideHeader($flag = true)
    {
        $this->hideHeader = (bool)$flag;
    }

    public function HideHeaderImage($flag = true)
    {
        $this->hideHeaderImage = (bool)$flag;
    }

    public function Header()
    {
        $x = $this->x;
        $y = $this->y;
        $this->SetXY(1, 1);

        if ($this->printHeaderSign) {
            $this->SetFont('ArialUU', '', 5);
            $color = $this->TextColor;
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 5, $this->awery_text, 0, 0, 'R', false, 'http://www.awery.aero');
            $this->TextColor = $color;

            $this->SetXY(1, 5);
        }

        if ($this->hideHeader) {
            return;
        }

        if ($this->textHeader !== null) {
            $this->SetXY($this->lMargin, 5);
            $this->SetFont('ArialUU', '', 12);
            $this->SetX(30);
            $this->Cell(0, 5, $this->textHeader, 0, 1, 'L');
            $this->Ln(10);

            if ($this->subject) {
                $this->SetFont('ArialUU', 'BU', 16);
                $this->MultilineCell($this->w - $this->lMargin - $this->rMargin, 8, $this->subject, 0, 1, 'C');
                $this->SetXY($this->lMargin, $this->GetY() + 6.5);
            }

            if ($this->sign) {
                $this->Ln(1);
                $this->SetFont('ArialUU', 'B', 12);
                $this->CenterText($this->GetY(), $this->sign);
                $this->SetXY($this->lMargin, $this->GetY() + 5);
            }

            if ($this->showPageNo) {
                $this->SetFont('ArialUU', 'B', 9);
                $this->Cell(0, 5, 'Page ' . $this->page . ' of {nb}', 0, 1, 'R');
            }

            return;
        }

        if (!$this->hideHeaderImage) {
            if ($this->CurOrientation == 'L') {
                $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '6500_280.png';
                $imageHeight = 12.66;
            } else {
                $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '4421_739_bw.png';
                $imageHeight = Zend_Registry::get('realm_name') == 'chapman' ? 50 : 33.43;
            }
//            \Zend_Debug::dump($image);
            if (file_exists($image)) {
                $this->Image($image, 5, 5, $this->w - $this->lMargin - $this->rMargin, $imageHeight);
                $this->SetXY($this->lMargin, $imageHeight + 15);
            } else {
                $this->SetY($this->y + 3);
            }
        } else {
            $this->Ln(5);
        }

        if ($this->subject) {
            $this->SetFont('ArialUU', 'BU', 16);
            $this->MultilineCell($this->w - $this->lMargin - $this->rMargin, 8, $this->subject, 0, 1, 'C');
            //$this->CenterText($this->GetY(), $this->subject);
            $this->SetXY($this->lMargin, $this->GetY() + 6.5);
        }

        if ($this->sign) {
            $this->Ln(1);
            $this->SetFont('ArialUU', 'B', 12);
            $this->CenterText($this->GetY(), $this->sign);
            $this->SetXY($this->lMargin, $this->GetY());
        }

        if ($this->invoiceNumber !== null) {
            if ($this->reference) {
                $iw = $this->w - $this->lMargin - $this->rMargin / 3;
            } else {
                $iw = $this->w - $this->lMargin - $this->rMargin / 2;
            }

            $this->SetFont('ArialUU', 'B', 12);
            $this->Cell($iw, 0, 'No: ' . $this->invoiceNumber);

            if ($this->reference) {
                $this->Cell($iw, 5, $this->reference, 0, 0, 'C');
            }

            $date = ($this->invoiceDate) ? date('d-M-Y', strtotime($this->invoiceDate)) : date('d-M-Y');
            if ($this->invoiceId && $this->addInvoiceIdToDate) {
                $date .= '/' . $this->invoiceId;
            }
            if ($this->PageNo() > 1) {
                $date .= ', page: ' . $this->PageNo();
            }

            $due_date = '';
            if (!is_null($this->invoiceDueDate)) {
                $id = new DateTime($this->invoiceDate);
                $id = new DateTime($id->format('Y-m-d'));
                $idd = new DateTime($this->invoiceDueDate);
                if ($idd > $id) {
                    $due_date = 'Due Date: ' . $idd->format('d-M-Y') . '  ';
                }
            }

            $this->SetFont('ArialUU', 'B', $this->invoiceDateSize);
            $this->Cell(0, 0, $due_date . 'Date: ' . $date, 0, 1, 'R');
            $this->SetXY($this->lMargin, $this->GetY() + 5);
        } elseif ($this->showPageNo) {
            $this->SetFont('ArialUU', 'B', 9);
            if ($this->reference) {
                $this->Cell(0, 5, $this->reference, 0, 0, 'C');
            }
            $this->x = $this->lMargin;
            $this->Cell(0, 5, 'Page ' . $this->page . ' of {nb}', 0, 1, 'R');
        } elseif ($this->reference) {
            $this->SetFont('ArialUU', 'B', 12);
            $y = $this->GetY();
            $this->CenterText($this->GetY() + 4.5, $this->reference);
            $this->SetXY($this->lMargin, $y);
        }
    }

    public function getHeaderHeight()
    {
        $height = 0;

        if ($this->hideHeader) {
            return 5;
        }

        if ($this->subject) {
            $height += 6.5;
        }

        if ($this->sign) {
            $height += 5;
        }

        if ($this->textHeader !== null) {
            if ($this->showPageNo) {
                $height += 5;
            }
            $height += 20;
            return $height;
        }

        if (!$this->hideHeaderImage) {
            if ($this->CurOrientation == 'L') {
                $image = Zend_Registry::get('document_root') . '/decoration/reports/' . $this->prefix . '6500_280.png';
                $imageHeight = 12.66;
            } else {
                $image = Zend_Registry::get('document_root') . '/decoration/reports/' . $this->prefix . '4421_739_bw.png';
                $imageHeight = 33.43;
            }
            if (file_exists($image)) {
                $height += $imageHeight + 15;
            } else {
                $height += 3;
            }
        } else {
            $height += 5;
        }

        if ($this->reference) {
            $height += 3;
        }

        if ($this->invoiceNumber !== null OR $this->showPageNo) {
            $height += 5;
        }
    }

    public function HideFooter($flag = true)
    {
        $this->disableFooter = (bool)$flag;
    }

    public function Footer()
    {
        if ($this->disableFooter) {
            return;
        }

        $pageBreak = 0;
        $lineY = 1;
        if ($this->CurOrientation != 'L') {
            $footerImage = Zend_Registry::get('document_root') . '/decoration/reports/' . $this->prefix . '4421_260_footer.png';
            #todo - поправить этот путь
            $footerText = AWERY_PATH . '/src/Reports/templates_pdf/' . $this->prefix . 'footer.txt';
            if (file_exists($footerImage)) {
                $lineY = $this->h - 12;
                $pageBreak = $lineY + 4;
                $imageX = ($this->w) / 2 - (200 / 2);
                $this->Image($footerImage, $imageX, $lineY, 200, 12);
            } elseif (file_exists($footerText)) {
                $data = file($footerText);
                $lines = count($data);
                $lineY = ($this->h - $lines * 5.5);
                $pageBreak = $lineY + 4;

                $this->SetFont('Times', '', 10);

                $textY = $lineY + 0.5;
                foreach ($data as $line) {
                    $textY = $textY + 4;
                    $this->CenterText($textY, $line);
                }
            } else {
                return;
            }

            //$this->Line($this->lMargin, $lineY, $this->w - $this->rMargin, $lineY);
        }

        if ($this->signAlert == true) {
            $this->SetFont('ArialUU', 'B', 10);
            $this->CenterText($lineY - 5, '(This is a computer generated Document, signature is not required)');
        }

        if (!$this->printHeaderSign) {
            $this->SetFont('ArialUU', '', 5);
            $this->CenterText($lineY - 2, $this->awery_text);
        }


//        $this->SetAutoPageBreak(true, $pageBreak);
    }

    public function GetFooterHeight()
    {
        if ($this->disableFooter) {
            return 5;
        }

        if ($this->CurOrientation != 'L') {
            $footerImage = Zend_Registry::get('document_root') . '/decoration/reports/' . $this->prefix . '4421_260_footer.png';
            $footerText = AWERY_PATH . '/src/Reports/templates_pdf/' . $this->prefix . 'footer.txt';
            if (file_exists($footerImage)) {
                $height = 12;
            } elseif (file_exists($footerText)) {
                $data = file($footerText);
                $lines = count($data);
                $height = $lines * 4;
            } else {
                $height = 0;
            }
        } else {
            $height = 0;
        }

        if ($this->signAlert == true) {
            $height += 8;
        }

        return $height;
    }

    public function startCalculatingCellHeight()
    {
        $this->calculatedHeight = 0;
        $this->calculation = true;
    }

    public function getCalculatedHeight($stop = true)
    {
        if ($stop === true) {
            $this->calculation = false;
        }
        return $this->calculatedHeight;
    }

    public function Ln($h = null, $calculate = true)
    {
        if ($this->calculation == true AND $calculate == true) {
//            if($h === null) {
//                $h = $this->lasth;
//            }
            $this->calculatedHeight += $h;
        }
        return parent::Ln($h);
    }

    public function getStringHeight($str, $w, $lh)
    {
        if ($this->GetStringWidth($str) <= $w) {
            return $lh;
        }

        $pb_was = $this->AutoPageBreak;
        $pb_margin = $this->bMargin;

        $x_start = $this->x;
        $y_start = $this->y;

        $spaces = preg_replace('~(.{1})~', ' ', $str);

        $prev_calc = $this->calculation;
        $prev_calc_height = $this->calculatedHeight;

        $cur_mLeft = $this->lMargin;
        $cur_mRight = $this->rMargin;

        $this->SetLeftMargin(0);
        $this->SetRightMargin($this->w - $w);
        $this->SetY($this->h + 10);

        $this->SetAutoPageBreak(false);
        $this->startCalculatingCellHeight();
        $this->Write($lh, $str);
        $str_height = $this->getCalculatedHeight();

//        $this->SetXY(0, $y_start);
//        $this->SetFillColor(255, 255, 255);
//        $this->Rect(0, $y_start, $w, $str_height, 'F');

        $this->calculation = $prev_calc;
        $this->calculatedHeight = $prev_calc_height;

        $this->SetAutoPageBreak($pb_was, $pb_margin);
        $this->SetLeftMargin($cur_mLeft);
        $this->SetRightMargin($cur_mRight);

        $this->SetXY($x_start, $y_start);

        return $str_height;
    }

    /*
     * second param is array($line_height, $cell_height)
     */
    public function MultilineCell($w, $lh = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $calculate = true)
    {
        $text = $txt;

        if (is_array($lh)) {
            $cell_h = $lh[1];
            $lh = $lh[0];
        }

        if ($this->GetStringWidth($txt) > $w) {
            $x_start = $this->x;
            $y_start = $this->y;

            $prev_calc = $this->calculation;
            $prev_calc_height = $this->calculatedHeight;

            $cur_mLeft = $this->lMargin;
            $cur_mRight = $this->rMargin;

            $this->SetLeftMargin($this->x);
            $this->SetRightMargin((int)$this->w - ($this->x + $w));

            $txt_h = $this->getStringHeight($txt, $w, $lh);
            if (isset($cell_h) AND $txt_h < $cell_h) {
                $this->y = $this->y + (($cell_h - $txt_h) / 2);
            }
            $this->startCalculatingCellHeight();
            $this->Write($lh, $txt);
            if (!isset($cell_h)) {
                $cell_h = $this->getCalculatedHeight();
            }

            $this->calculation = $prev_calc;
            $this->calculatedHeight = $prev_calc_height;

            $this->SetLeftMargin($cur_mLeft);
            $this->SetRightMargin($cur_mRight);

            $this->SetXY($x_start, $y_start);
            $txt = '';
        } elseif (!isset($cell_h)) {
            $cell_h = $lh;
        }

        if ($this->calculation == true AND $calculate == true) {
            $this->calculatedHeight += $cell_h * 2;
        }

        return parent::Cell($w, $cell_h, $txt, $border, $ln, $align, $fill, $link);
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $calculate = true)
    {
        if ($this->calculation == true AND $calculate == true) {
            $this->calculatedHeight += $h;
        }

        return parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    public function Th($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $calculate = true)
    {
        $words = explode("\n", $txt);
        if (empty($words)) {
            return parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $calculate);
        }

        $x_before = $this->x;
        $y_before = $this->y;

        parent::Cell($w, $h, '', $border, $ln, $align, $fill, $link, $calculate);

        $set_x = $this->x;
        $set_y = $this->y;

        $this->SetXY($x_before, $y_before - 0.25);
        $lMargin = $this->lMargin;
        $rMargin = $this->rMargin;
        $this->SetLeftMargin($this->x);
        $this->SetRightMargin($this->w - ($this->x + $w));
        $y_plus = $h / 2 - 0.5;
        foreach ($words as $word) {
            if ($align == 'C') {
                $this->CenterText($this->y + $y_plus, $word);
            } elseif ($align == 'R') {
                $pos = $x_before + $w - 1 - $this->GetStringWidth($word);
                $this->Text($pos, $this->y + $y_plus, $word);
            } else {
                $this->Text($this->x, $this->y + $y_plus, $word);
            }
            $y_plus += $y_plus;
        }
        $this->SetLeftMargin($lMargin);
        $this->SetRightMargin($rMargin);

        $this->y = $set_y;
        $this->x = $set_x;
    }

//    public function  Text($x, $y, $txt) {
//        if ($this->calculation == true AND $calculate == true) {
//            $this->calculatedHeight += $h;
//        }
//        return parent::Text($x, $y, $txt);
//    }

    public function AddPage($orientation = '', $format = '')
    {
//        if (isset($this->pages[$this->page + 1])) {
//            $this->page++;
//            $this->SetY($this->getHeaderHeight());
//        } else {
        parent::AddPage($orientation = '', $format = '');
//        }
    }

    public function _getfontpath()
    {
        if (!defined('FPDF_FONTPATH') && is_dir(dirname(__FILE__) . '/font')) {
            define('FPDF_FONTPATH', dirname(__FILE__) . '/font/');
        }
        $path = defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    //TODO - remove this method!!!!! - to anton
    public function horizontalTable(array $labels, array $data, $config = array())
    {
        $defaults = array(
            'label_bold' => true,
            'label_width' => 25,
            'label_border' => 0,
            'label_align' => 'R',
            'cell_bold' => false,
            'cell_underline' => false,
            'cell_width' => 0,
            'cell_border' => 0,
            'cell_align' => 'L',
            'use_colon' => true,
            'height' => 8,
            'hide_empty' => true,
        );

        $config = array_merge($defaults, $config);
        if ($config['cell_width'] == 0) {
            $config['cell_width'] = (int)$this->w - $this->lMargin - $this->rMargin - $config['label_width'];
        }

        foreach ($labels as $label => $field) {
            if (is_array($field)) {
                $string_code = str_replace('__VAL__', '$data[$field[1]]', $field[0]) . ';';
                $value = '';
                if (isset($data[$field[1]])) {
                    eval('$value = ' . $string_code . ';');
                }
            } else {
                $value = (isset($data[$field])) ? $data[$field] : '';
            }

            if (empty($value) AND $config['hide_empty']) {
                continue;
            }

            if ($config['use_colon']) {
                $label .= ':';
            }

            $this->SetFont($this->FontFamily, ($config['label_bold']) ? 'B' : '', $this->FontSizePt);
            $this->Cell($config['label_width'], $config['height'], $label, $config['label_border'], 0, $config['label_align']);
            $font_style = '';
            if ($config['cell_bold']) $font_style .= 'B';
            if ($config['cell_underline']) $font_style .= 'U';
            $this->SetFont($this->FontFamily, $font_style, $this->FontSizePt);
            $padding_height = floor($config['height'] / 3);
            $line_height = $this->getStringHeight($value, $config['cell_width'], $config['height'] - $padding_height);
            if ($line_height > $config['height']) {
                $this->y = $this->y + $padding_height / 2;
                $this->MultilineCell($config['cell_width'], array($config['height'] - $padding_height, $line_height), $value, $config['cell_border'], 1, $config['cell_align']);
            } else {
                $this->Cell($config['cell_width'], $config['height'], $value, $config['cell_border'], 1, $config['cell_align']);
            }
        }
    }

    public function table(array $map, array $data, $config = array(), $totals = array())
    {
        $defaults = array(
            'caption' => false,
            'caption_right' => false,
            'caption_right_size' => 3,
            'caption_right_bold' => true,
            'page_break_text' => false,
            'page_break_margin' => 0,
            'margin_left' => 5,
            'margin_right' => 5,
            'multiline' => false,
            'border' => 1,
            'color' => array(0, 0, 0),
            'label_bold' => true,
            'label_align' => 'C',
            'label_height' => 8,
            'cell_bold' => false,
            'cell_align' => 'C',
            'cell_height' => 5,
            'hide_empty' => true,
            'style' => 'awery',
            'totals_label' => 'TOTAL:',
            'totals_label_align' => 'R',
            'totals_height' => 6,
            'totals_towords' => false,
            'totals_format' => array(),
        );

        $config = $config + $defaults;

        $this->_tableHeader($map, $config);

        if (isset($config['style']) && $config['style'] == 'awery') {
            $this->Ln(1);
            $this->SetDrawColor(136, 136, 136);
        }

        $font_style = '';
        if (@$config['cell_bold'] && @$config['bold']) $font_style .= 'B';
        if (@$config['underline']) $font_style .= 'U';
        $this->SetFont($this->FontFamily, $font_style, $this->FontSizePt);

        $footer_height = $this->GetFooterHeight();
        $count_fields = count($map);
        $totals_val = $complited = array();

        $lineheight = $config['cell_height'];
        if ($config['multiline']) {
            $ii = 0;
            foreach ($data as $row_index => $row_data) {
                $kk = 0;
                $ii++;
                foreach ($map as $row_field => $row_params) {
                    $kk++;

                    if (isset($row_params['value'])) {
                        eval('$row_data[$row_field] = ' . str_replace('$row', '$row_data', $row_params['value']) . ';');
                    }

                    if (isset($row_params['format'])) {
                        $string_code = str_replace(array('$i', '__VAL__'), array('$ii', '$row_data[$row_field]'), $row_params['format']) . ';';
                        eval('$value = ' . $string_code . ';');
                    } else {
                        $value = (isset($row_data[$row_field])) ? $row_data[$row_field] : '';
                    }

                    if (isset($row_params['exec'])) {
                        eval(str_replace('__VAL__', '$row_data[$row_field]', $row_params['exec']) . ';');
                    }

                    $cellH = $this->getStringHeight($value, $row_params['width'], $config['cell_height'] - 0.5);
                    $lineheight = ($cellH > $lineheight) ? $cellH : $lineheight;
                }
            }
        }

        $i = 0;
        foreach ($data as $row) {
            $k = 0;
            $i++;
            foreach ($map as $field => $params) {
                $k++;

                if ($this->GetY() > ($this->h - ($footer_height + 10))) {
                    $current_margin_left = $this->lMargin;
                    $current_margin_right = $this->rMargin;

                    $this->SetLeftMargin($config['margin_left']);
                    $this->SetRightMargin($config['margin_right']);
                    $this->AddPage();
                    if ($config['page_break_text']) {
                        $this->SetY($this->y + $config['page_break_margin']);
                        $def_size = $this->FontSizePt;
                        $this->SetFont($this->FontFamily, 'B', $this->FontSizePt + 3);
                        $this->Cell(0, 6, $config['page_break_text'], 0, 1, 'C');
                        $this->SetFont($this->FontFamily, '', $def_size);
                    }
                    $this->SetLeftMargin($current_margin_left);
                    $this->SetRightMargin($current_margin_right);
                    $this->SetDrawColor(0, 0, 0);
                    $this->_tableHeader($map, $config);
                    if ($config['style'] == 'awery') {
                        $this->Ln(1);
                        $this->SetDrawColor(136, 136, 136);
                    }
                }

                $font_style = '';
                if (@$config['cell_bold'] AND @$params['bold']) $font_style .= 'B';
                if (@$params['underline']) $font_style .= 'U';
                $this->SetFont($this->FontFamily, $font_style, $this->FontSizePt);
                $this->SetTextColor($config['color'][0], $config['color'][1], $config['color'][2]);

//                if (!isset($complited[$field])) {
                if (isset($params['value'])) {
                    if (!preg_match('~\$row~', $params['value'])) {
                        $row[$field] = $params['value'];
                    } else {
                        eval('$row[$field] = ' . $params['value'] . ';');
                    }
                }

                if (isset($params['format'])) {
                    $string_code = str_replace('__VAL__', '$row[$field]', $params['format']) . ';';
                    eval('$value = ' . $string_code . ';');
                } else {
                    $value = (isset($row[$field])) ? $row[$field] : '';
                }

                if (isset($params['exec'])) {
                    eval(str_replace('__VAL__', '$row[$field]', $params['exec']) . ';');
                }
//                } else {
//                    $value = $complited[$field];
//                }

                if (in_array($field, $totals)) {
                    if (!isset($totals_val[$field])) {
                        $totals_val[$field] = 0;
                    }

                    $cur_total_val = (isset($row[$field])) ? $row[$field] : $value;
                    if (!isset($config['totals_format'][$field])) {
                        $totals_val[$field] = $totals_val[$field] + $cur_total_val;
                    } else {
                        eval('$totals_val[$field] = ' . $config['totals_format'][$field] . ';');
                    }
                }

                $align = ($params['align']) ? $params['align'] : $config['label_align'];
                if (!$config['multiline']) {
                    $this->Cell($params['width'], $config['cell_height'], $value, $config['border'], ($k == $count_fields) ? 1 : 0, $align);
                } else {
                    $this->MultilineCell($params['width'], array($config['cell_height'] - 0.5, $lineheight), $value, $config['border'], ($k == $count_fields) ? 1 : 0, $align);
                }
            }
        }

        if (!empty($totals_val)) {
            $this->Ln(1);
            $this->SetDrawColor(0, 0, 0);
            $this->SetFont($this->FontFamily, ($config['label_bold']) ? 'B' : '', $this->FontSizePt);
            $this->SetTextColor($config['color'][0], $config['color'][1], $config['color'][2]);

            $totals_width = array();
            $count_fields = count($map);
            $map_keys = array_keys($map);
            foreach ($map as $field => $params) {
                if (!isset($current_field)) {
                    $current_field = $field;
                }

                if (!in_array($field, $totals)) {
                    if (!isset($totals_width[$current_field])) {
                        $totals_width[$current_field] = 0;
                    }

                    $totals_width[$current_field] += $map[$field]['width'];
                } else {
                    $cur_index = array_search($field, $map_keys);
                    $totals_width[$map_keys[$cur_index]] = $map[$field]['width'];
                    $current_field = @$map_keys[$cur_index + 1];
                }
            }

            $count_totals = count($totals_width);
            $ik = 0;
            foreach ($totals_width as $tf => $width) {
                $ik++;

                if (isset($map[$tf]['exec'])) {
                    eval(str_replace('__VAL__', '$row[$field]', $map[$tf]['exec']) . ';');
                }

                if ($ik == 1 AND !isset($totals_val[$tf])) {
                    $txt = $config['totals_label'];
                    if (is_array($config['totals_towords'])) {
                        $txt .= ' ' . to_words($totals_val[$config['totals_towords'][0]], $config['totals_towords'][1]);
                    }
                    $align = $config['totals_label_align'];
                } else {
                    $txt = @$totals_val[$tf];
                    if (isset($map[$tf]['format']) && !isset($map[$tf]['skip_format_for_total'])) {
                        $string_code = str_replace('__VAL__', '$txt', $map[$tf]['format']) . ';';
                        eval('$txt = ' . $string_code . ';');
                    }
                    $align = (@$map[$tf]['align']) ? $map[$tf]['align'] : $config['label_align'];
                }

                $this->Cell($width, $config['totals_height'], $txt, $config['border'], ($ik == $count_totals) ? 1 : 0, $align);
            }
        }
    }

    protected function _tableHeader(array $map, array $config)
    {
        $def_size = $this->FontSizePt;
        $caption_size = $this->FontSizePt + 3;
        if ($config['caption']) {
            $this->SetFont($this->FontFamily, 'B', $caption_size);
            $this->Cell($this->w / 2, 8, $config['caption'], 0, ($config['caption_right']) ? 0 : 1);
        }
        if ($config['caption_right']) {
            $this->SetFont($this->FontFamily, ($config['caption_right_bold']) ? 'B' : '', $def_size + $config['caption_right_size']);
            $this->Cell(0, 8, $config['caption_right'], 0, 1, 'R');
        }
        $this->SetFont($this->FontFamily, ($config['label_bold']) ? 'B' : '', $def_size);
        $i = 0;
        $count_fields = count($map);
        $this->SetDrawColor(0, 0, 0);
        foreach ($map as $field => $params) {
            $i++;

            if (!is_numeric(@$params['width'])) {
                throw new Exception('Column width for "' . $field . '" is invalid!');
            }

            if (!is_string(@$params['label'])) {
                $params['label'] = ucwords(str_replace('_', ' ', $field));
            }

            $method = 'Cell';
            if (mb_strpos($params['label'], "\n") !== false) {
                $method = 'Th';
            }

            $this->$method($params['width'], $config['label_height'], $params['label'], $config['border'], ($i == $count_fields) ? 1 : 0, $config['label_align']);
        }
    }

    public function Output($name = '', $dest = '')
    {


        // if protection enabled
        $encryption = Zend_Registry::isRegistered("pdf_global_encryption") ? Zend_Registry::get("pdf_global_encryption") : false;

        if ($encryption) {
            $permissions = Zend_Registry::get("pdf_global_permissions_array");
            $user_pass = "";
            $owner_pass = Zend_Registry::get("pdf_edit_password"); // or null for autogeneration
            $this->SetProtection($permissions, $user_pass, $owner_pass);
        }

        //Output PDF to some destination
        if ($this->state < 3)
            $this->Close();
        $dest = strtoupper($dest);
        if ($dest == '') {
            if ($name == '') {
                $name = 'doc.pdf';
                $dest = 'I';
            } else
                $dest = 'I';
        }
        switch ($dest) {
            case 'I':
                //Send to standard output
                if (ob_get_length())
                    $this->Error('Some data has already been output, can\'t send PDF file');
                if (php_sapi_name() != 'cli') {
                    //We send to a browser
                    header('Content-Type: application/pdf');
                    if (headers_sent())
                        $this->Error('Some data has already been output, can\'t send PDF file');
                    header('Content-Length: ' . strlen($this->buffer));
                    header('Content-Disposition: inline; filename="' . $name . '"');
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    ini_set('zlib.output_compression', '0');
                }
                echo $this->buffer;
                break;
            case 'D':
                //Download file
                if (ob_get_length())
                    $this->Error('Some data has already been output, can\'t send PDF file');
                header('Content-Type: application/x-download');
                if (headers_sent())
                    $this->Error('Some data has already been output, can\'t send PDF file');
                header('Content-Length: ' . strlen($this->buffer));
                header('Content-Disposition: attachment; filename="' . $name . '"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                ini_set('zlib.output_compression', '0');
                echo $this->buffer;
                break;
            case 'F':
                //Save to local file
                $f = fopen($name, 'wb');
                if (!$f)
                    $this->Error('Unable to create output file: ' . $name);
                fwrite($f, $this->buffer, strlen($this->buffer));
                fclose($f);
                break;
            case 'S':
                //Return as a string
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: ' . $dest);
        }
        return '';
    }

    /*
     * Функция обрезания строки с добавлением троеточия или набора символов в конце
     * 
     * TODO:
     * решить вопрос с padding (default value)
     */
    public function CutStringByLength($string, $width_needed, $padding = 2, $endsymbs = "...")
    {
        $width = $this->GetStringWidth($string . $endsymbs) + $padding;
        if ($this->GetStringWidth($string) > $width_needed) {
            $k = 0;
            while ($width > $width_needed && $k < 100) {
                //$string = preg_replace("~\s[a-zA-Z0-9\-\.\']*$~u", "", $string);
                $string = preg_replace("~\s([^\s]*)$~u", "", $string);
                $width = $this->GetStringWidth($string . $endsymbs) + $padding;
                $k++;
                /*
                $words = explode(" ", $string);
                array_pop($chars);
                $string = implode(" ", $chars)
                */
            }
            return $string . $endsymbs;
        } else {
            return $string;
        }
        //return $string . ($this->GetStringWidth($string . $endsymbs) + $padding);
    }
}