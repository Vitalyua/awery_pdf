<?php
require_once 'FPDF_Protection.php';

//if ( !defined('FPDF_FONTPATH') )
//    define('FPDF_FONTPATH', dirname(__FILE__).'/font/');
define('CCDATE', date('Y M d H:i'));
//if ( !defined('_SYSTEM_TTFONTS') )
//    define("_SYSTEM_TTFONTS", FPDF_FONTPATH."unifont/");


class AweryPdf extends FPDF_Protection
{

    public $unifontSubset;
    public $page;               // current page number
    public $n;                  // current object number
    public $offsets;            // array of object offsets
    public $buffer;             // buffer holding in-memory PDF
    public $pages;              // array containing pages
    public $state;              // current document state
    public $compress;           // compression flag
    public $k;                  // scale factor (number of points in user unit)
    public $DefOrientation;     // default orientation
    public $CurOrientation;     // current orientation
    public $StdPageSizes;       // standard page sizes
    public $DefPageSize;        // default page size
    public $CurPageSize;        // current page size
    public $CurRotation;        // current page rotation
    public $PageInfo;           // page-related data
    public $wPt, $hPt;          // dimensions of current page in points
    public $w, $h;              // dimensions of current page in user unit
    public $lMargin;            // left margin
    public $tMargin;            // top margin
    public $rMargin;            // right margin
    public $bMargin;            // page break margin
    public $cMargin;            // cell margin
    public $x, $y;              // current position in user unit
    public $lasth;              // height of last printed cell
    public $LineWidth;          // line width in user unit
    public $fontpath;           // path containing fonts
    public $CoreFonts;          // array of core font names
    public $fonts;              // array of used fonts
    public $FontFiles;          // array of font files
    public $encodings;          // array of encodings
    public $cmaps;              // array of ToUnicode CMaps
    public $FontFamily;         // current font family
    public $FontStyle;          // current font style
    public $underline;          // underlining flag
    public $CurrentFont;        // current font info
    public $FontSizePt;         // current font size in points
    public $FontSize;           // current font size in user unit
    public $DrawColor;          // commands for drawing color
    public $FillColor;          // commands for filling color
    public $TextColor;          // commands for text color
    public $ColorFlag;          // indicates whether fill and text colors are different
    public $WithAlpha;          // indicates whether alpha channel is used
    public $ws;                 // word spacing
    public $images;             // array of used images
    public $PageLinks;          // array of links in pages
    public $links;              // array of internal links
    public $AutoPageBreak;      // automatic page breaking
    public $PageBreakTrigger;   // threshold used to trigger page breaks
    public $InHeader;           // flag set when processing header
    public $InFooter;           // flag set when processing footer
    public $AliasNbPages;       // alias for total number of pages
    public $ZoomMode;           // zoom display mode
    public $LayoutMode;         // layout display mode
    public $metadata;           // document properties
    public $CreationDate;       // document creation date
    public $PDFVersion;         // PDF version number

    public $awery_text = 'Awery Aviation Solutions - www.awery.aero / ';
    public $printHeaderSign = true;

    public $signAlert = false;
    public $prefix = '';

    public $calculation = false;
    public $calculatedHeight = 0;

    public $invoiceId = null;
    public $invoiceDateSize = 12;
    public $invoiceNumber = null;
    public $invoiceDate = null;
    public $invoiceDueDate = null;

    public $addInvoiceIdToDate = true;

    public $reference = null;

    public $textHeader = null;
    public $disableFooter = false;

    public $sign, $subject;
    public $showPageNo = false;

    public $hideHeader = false;
    public $hideHeaderImage = false;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
//        if (!file_exists(FPDF_FONTPATH)) {
//            mkdir(FPDF_FONTPATH, 0700, true);
//        }
//        if (!file_exists(_SYSTEM_TTFONTS)) {
//            mkdir(_SYSTEM_TTFONTS, 0700, true);
//        }
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
            $this->_CenterText($text, $y);
            return;
        }


        if ($y == null) {
            $y = $this->y;
        }

        $this->Text($this->GetCenterPos($text), $y, $text);
    }

    public function SetSubject($subject, $isUTF8 = false, $sign = false)
    {
        if ($isUTF8)
            $subject = $this->_UTF8toUTF16($subject);
        $this->subject = $subject;
        parent::SetSubject($subject, $isUTF8);
        $this->setSign($sign);
    }

    public function setSign($sign)
    {
        $this->sign = $sign;
        $this->metadata['Sign'] = $sign;
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

    /**
     * @param $dr_rate
     * @param $cr_rate
     * @param $draw
     * @param $currency_id
     * @param $dr_cr_id
     * @return string|void
     * @deprecated
     */
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
            $text = 'Currency rate: 1 ' . \Acc\Info::getCurrencyName($dr_cr_id) . ' = ' . $dr_rate . ' ' . \Acc\Info::getCurrencyName($currency_id);
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
                if(file_exists(APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '6500_280.png'))
                    $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '6500_280.png';
                else
                    $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . 'header_horizontal.png';
                $imageHeight = 12.66;
            } else {
                if(file_exists(APPLICATION_PATH . '/decoration/reports/' . $this->prefix . 'header.png'))
                    $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . 'header.png';
                else
                    $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '4421_739_bw.png';
//                $imageHeight = Zend_Registry::get('realm_name') == 'chapman' ? 50 : 33.43;
                $imageHeight = 33.43;
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
                $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '6500_280.png';
                $image2 = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . 'header_horizontal.png';
                $imageHeight = 12.66;
            } else {
                $image = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '4421_739_bw.png';
                $image2 = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . 'header.png';
                $imageHeight = 33.43;
            }
            if (file_exists($image) || file_exists($image2)) {
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
        return $height;
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
            if(file_exists(APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '4421_260_footer.png')) {
                $footerImage = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '4421_260_footer.png';
            } else {
                $footerImage = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . 'footer.png';
            }
            #todo - поправить этот путь
            $footerText = '/src/Reports/templates_pdf/' . $this->prefix . 'footer.txt';
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
            $footerImage = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . '4421_260_footer.png';
            $footerImage2 = APPLICATION_PATH . '/decoration/reports/' . $this->prefix . 'footer.png';
            $footerText = '/src/Reports/templates_pdf/' . $this->prefix . 'footer.txt';
            if (file_exists($footerImage) || file_exists($footerImage2)) {
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

        parent::Cell($w, $cell_h, $txt, $border, $ln, $align, $fill, $link);
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $calculate = true)
    {
        if ($this->calculation == true AND $calculate == true) {
            $this->calculatedHeight += $h;
        }

        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    public function Th($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $calculate = true) :void
    {
        $words = explode("\n", $txt);
        if (empty($words)) {
            parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $calculate);
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

    public function _getfontpath()
    {
        if (!defined('FPDF_FONTPATH') && is_dir(dirname(__FILE__) . '/font')) {
            define('FPDF_FONTPATH', dirname(__FILE__) . '/font/');
        }
        $path = defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function Output($name = '', $dest = '', $isUTF8 = false)
    {


        // if protection enabled
        $encryption = \Awery\Globals::GI()->get('pdf_global_encryption',false);

        if ($encryption) {
            $permissions = \Awery\Globals::GI()->get('pdf_global_permissions_array');
            $user_pass = "";
            $owner_pass = \Awery\Globals::GI()->get('pdf_edit_password'); // or null for autogeneration
            $this->SetProtection($permissions, $user_pass, $owner_pass);
        }
        return parent::Output($name, $dest, $isUTF8);
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
