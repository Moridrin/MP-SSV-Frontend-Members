<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 2016-10-21
 * Time: 06:56
 */
//require_once('fpdf.php');
require_once('MultiCellBlt.php');

class SSV_FPDF extends FPDF
{
    private $fourColumns = array(30, 45, 30, 45);

    private $pageLMargin;
    private $pageRMargin;
    private $pageTMargin;
    private $pageBMargin;

    const ALIGN_LEFT = 'L';
    const ALIGN_CENTER = 'C';
    const ALIGN_RIGHT = 'R';

    const COLOR_TEXT = 1;
    const COLOR_TEXT_LIGHT_1 = 2;
    const COLOR_TEXT_LIGHT_2 = 3;
    const COLOR_TEXT_WHITE = 4;
    const COLOR_DRAW = 5;
    const COLOR_DRAW_ACCENT = 6;
    const COLOR_FILL = 7;
    const COLOR_FILL_ACCENT = 8;

    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        parent::SetFont('Arial');
        $this->pageLMargin = $this->lMargin;
        $this->pageRMargin = $this->rMargin;
        $this->pageTMargin = $this->tMargin;
        $this->pageBMargin = $this->bMargin;
        $this->lMargin     = $this->lMargin + 25 + $this->lMargin;
        $this->tMargin     = $this->tMargin + 37 + $this->tMargin;
    }

    /**
     * @param FrontendMember $frontendMember
     *
     * @return $this
     */
    public function build($ABSPATH,
                          $first_name,
                          $initials,
                          $last_name,
                          $gender,
                          $iban,
                          $date_of_birth,
                          $street,
                          $email,
                          $postal_code,
                          $city,
                          $phone_number,
                          $emergency_phone)
    {
        //Start
        $this->AddPageWithFormat($ABSPATH);
        parent::SetFont('Arial', '', 26);
        $this->Write('Agreement Direct Debit SEPA (Automatische Incasso)', true, 12);
        $this->whitespace(4);

        //Personal Details
        parent::SetFont('Arial', '', 8);
        parent::SetTextColor(0, 0, 0);
        $this->WriteCell(0, 'Collector: ');
        $this->WriteCell(array(1,2), 'Eerste Studenten All Terrain Sportvereniging');
        $this->whitespace(6);
        $this->WriteCell(0, 'Collector ID: ');
        $this->WriteCell(1, 'NL19ZZZ402398740000');
        $this->whitespace(6);

        parent::SetFont('Arial', '', 18);
        parent::SetTextColor(62, 118, 42);
        $this->whitespace(4);
        $this->Write('Personal Details', true, 8);
        parent::SetFont('Arial', '', 8);
        parent::SetTextColor(0, 0, 0);

        $this->WriteCell(0, 'First Name: ');
        $this->WriteCell(1, $first_name);
        $this->WriteCell(2, 'Initials: ');
        $this->WriteCell(3, $initials, array(), false);
        $this->WriteCell(0, 'Last Name: ');
        $this->WriteCell(1, $last_name);
        $this->WriteCell(2, 'Gender: ');
        $this->WriteCell(3, $gender, array(), false);
        $this->WriteCell(0, 'Bank Account (IBAN): ');
        $this->WriteCell(1, $iban);
        $this->WriteCell(2, 'Date of Birth: ');
        $this->WriteCell(3, $date_of_birth, array(), false);
        $this->WriteCell(0, 'Street + Number: ');
        $this->WriteCell(1, $street);
        $this->WriteCell(2, 'Email: ');
        $this->WriteCell(3, $email, array(), false);
        $this->WriteCell(0, 'Postal Code: ');
        $this->WriteCell(1, $postal_code);
        $this->WriteCell(2, 'City and Country: ');
        $this->WriteCell(3, $city . ' The Netherlands', array(), false);
        $this->WriteCell(0, 'Phone: ');
        $this->WriteCell(1, $phone_number);
        $this->WriteCell(2, 'Emergency Contact: ');
        $this->WriteCell(3, $emergency_phone, array(), false);

        parent::SetFont('Arial', '', 18);
        parent::SetTextColor(62, 118, 42);
        $this->whitespace(4);
        $this->Write('Conditions', true, 8);
        parent::SetFont('Arial', '', 8);
        parent::SetTextColor(0, 0, 0);
        $this->WriteCell(array(0,1,2,3), 'The Direct Debit arrangement makes it easier for both All Terrain and its members to handle payments of membership, but also payments of other activities. A payment therefore won\'t require cash payments or manual bank transfers, but will automatically be cashed with your written or verbal permission.  The rules are listed below. These rules contain the rights of the payee and the obligations for All Terrain.', array(), false);
        $this->whitespace(6);
        $this->WriteCell(array(0,1,2,3), 'You could always retain your money without giving a reason, if you do not agree with the payment/depreciation. You have 56 days (8 weeks) to order your bank office to refund the money.', array('bullet' => chr(149)), false);
        $this->WriteCell(array(0,1,2,3), 'The treasurer will announce the transaction/payment at least two weeks and up to two months before the fee is deducted.', array('bullet' => chr(149)), false);
        $this->WriteCell(array(0,1,2,3), 'All Terrain may only deduct an amount other than the annual membership fee after you have given written or oral permission.', array('bullet' => chr(149)), false);
        $this->WriteCell(array(0,1,2,3), 'No money will be deducted if the funds in your account are not sufficient.', array('bullet' => chr(149)), false);
        $this->WriteCell(array(0,1,2,3), 'If you want to terminate the contract, report this to the treasurer by e-mail or letter to the above addresses.', array('bullet' => chr(149)), false);
        $this->whitespace(6);
        $this->WriteCell(array(0,1,2,3), 'By signing this mandate form, you authorize your bank to debit your account in accordance with the instructions from All Terrain. As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your agreement with you bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', array(), false);
        $this->whitespace(5);
        $this->WriteCell(0, 'Place and Date: ');
        $this->WriteCell(2, 'Signature: ');
        return $this;
    }

    public function WriteCell($column, $content, $args = array(), $return_to_start = true)
    {
        $align  = array_key_exists('align', $args) ? $args['align'] : self::ALIGN_LEFT;
        $border = array_key_exists('border', $args) ? $args['border'] : false;
        $fill   = array_key_exists('fill', $args) ? $args['fill'] : false;
        $table  = array_key_exists('table', $args) ? $args['table'] : $this->fourColumns;
        $isBulletCell  = array_key_exists('bullet', $args) ? ($args['bullet'] != false) : false;

        $y_start = $this->GetY();
        if (is_array($column)) {
            $this->SetX($this->lMargin + array_sum(array_slice($table, 0, $column[0])));
            $width = 0;
            foreach ($column as $item) {
                $width += $table[$item];
            }
            if ($isBulletCell) {
                $this->MultiCellBlt($width, 6, $args['bullet'], $content, $border ? 1 : 0, $align, $fill);
            } else {
                $this->MultiCell($width, 6, $content, $border ? 1 : 0, $align, $fill);
            }
        } else {
            $this->SetX($this->lMargin + array_sum(array_slice($table, 0, $column)));

            if ($isBulletCell) {
                $this->MultiCellBlt($table[$column], $args['bullet'], 6, $content, $border ? 1 : 0, $align, $fill);
            } else {
                $this->MultiCell($table[$column], 6, $content, $border ? 1 : 0, $align, $fill);
            }
        }
        if ($this->GetY() < $y_start) {
            $y_start = $this->tMargin;
        }
        $height = $this->GetY() - $y_start;
        if ($return_to_start) {
            $this->SetY($y_start);
        }
        return $height;
    }

    public function GetCellHeight($column, $content, $args = array(), $fontStyle = '', $fontSize = 11)
    {
        $align  = array_key_exists('align', $args) ? $args['align'] : self::ALIGN_LEFT;
        $border = array_key_exists('border', $args) ? $args['border'] : false;
        $table  = array_key_exists('table', $args) ? $args['table'] : $this->fourColumns;
        $height = $this->GetMultiCellHeight($table[$column], 6, $content, $border ? 1 : 0, $align);
        return $height;
    }

    public function AddPageWithFormat($ABSPATH, $orientation = '', $size = '')
    {
        parent::AddPage($orientation, $size);
        $this->Image($ABSPATH . '/wp-content/plugins/ssv-frontend-members/images/Document Header.png', $this->w - $this->pageRMargin - 75, $this->pageTMargin, 75, 37, 'PNG');
        $this->Image($ABSPATH . '/wp-content/plugins/ssv-frontend-members/images/Vertical Banner.png', $this->pageLMargin, $this->pageTMargin, 25, null, 'PNG');
        $this->SetXY($this->lMargin, $this->pageTMargin);

        // <editor-fold desc="Company Information">
        parent::SetFont('Arial', '', 9);
        $this->Write('Eerste Studenten All Terrain Sportvereniging', true, 4.75);
        $this->Write('Studentensportcentrum', true, 4.75);
        $this->Write('T.a.v. All Terrain', true, 4.75);
        $this->Write('Onze Lieve Vrouwestraat 1', true, 4.75);
        $this->Write('5612 AW Eindhoven', true, 4.75);
        $this->Write('board@allterrain.nl', true, 4.75);
        $this->Write('ING Bank: NL93 INGB 0000 129393', true, 4.75);
        $this->Write('Kamer Van Koophandel: 40239874', true, 4.75);
        $this->SetXY($this->lMargin, $this->tMargin);
        //</editor-fold>
    }

    public function SetTextRight($text, $height = 3.5, $border = 0, $ln = 1, $link = '')
    {
        $width = $this->GetStringWidth($text);
        $this->setXY($this->w - $this->rMargin - $width, $this->GetY());
        $this->Cell($width, $height, $text, $border, $ln, '', false, $link);
    }

    public function whitespace($size)
    {
        $this->SetXY($this->GetX(), $this->GetY() + $size);
    }

    public function newline()
    {
        $this->whitespace(6);
    }

    public function Write($txt, $ln = true, $h = 6, $link = '')
    {
        parent::Write($h, htmlspecialchars_decode($txt), $link);
        if ($ln) {
            $this->Ln();
        }
    }

    public function ConvertCurrency($string)
    {
        $string = str_replace('&euro;', chr(128), $string);
        return $string;
    }

    function MultiCellBlt($w, $h, $blt, $txt, $border=0, $align='J', $fill=false)
    {
        //Get bullet width including margins
        $blt_width = $this->GetStringWidth($blt)+$this->cMargin*2;

        //Save x
        $bak_x = $this->x;

        //Output bullet
        $this->Cell($blt_width,$h,$blt,0,'',$fill);

        //Output text
        $this->MultiCell($w-$blt_width,$h,$txt,$border,$align,$fill);

        //Restore x
        $this->x = $bak_x;
    }

    function GetMultiCellHeight($w, $h, $txt, $border = null, $align = 'J')
    {
        // Calculate MultiCell with automatic or explicit line breaks height
        // $border is un-used, but I kept it in the parameters to keep the call
        //   to this function consistent with MultiCell()
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s    = str_replace("\r", '', $txt);
        $nb   = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep    = -1;
        $i      = 0;
        $j      = 0;
        $l      = 0;
        $ns     = 0;
        $height = 0;
        while ($i < $nb) {
            // Get next character
            $c = $s[$i];
            if ($c == "\n") {
                // Explicit line break
                if ($this->ws > 0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                //Increase Height
                $height += $h;
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $ns  = 0;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
                $ls  = $l;
                $ns++;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                // Automatic line break
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                    if ($this->ws > 0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    //Increase Height
                    $height += $h;
                } else {
                    if ($align == 'J') {
                        $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
                        $this->_out(sprintf('%.3F Tw', $this->ws * $this->k));
                    }
                    //Increase Height
                    $height += $h;
                    $i = $sep + 1;
                }
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $ns  = 0;
            } else {
                $i++;
            }
        }
        // Last chunk
        if ($this->ws > 0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        //Increase Height
        $height += $h;

        return $height;
    }
}