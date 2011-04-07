<?php

function logsubstr($str, $pos, $len) {
    return substr($str, $pos - 1, $len+1);
}

class KID {

    function generateKIDmod10($pre, $prelen, $post, $postlen) {
        $calc = sprintf("%0".$prelen."d%0".$postlen."d", $pre, $post);

        $sum = 0;
        $weight = 2;
        for($pos = strlen($calc) - 1; $pos >= 0; $pos--) {
            $product = $calc[$pos] * $weight;

            for($i = 0; $i < strlen($product); $i++) {
                $sum += substr($product, $i, 1);
            }

            if($weight == 2) {
                $weight = 1;
            } else {
                $weight = 2;
            }

        }

        $check = 10 - substr($sum, -1, 1);

        return $calc.$check;
    }

    function readHeader($startRecord) {
        $header = array();

        $header["data_sender"] = logsubstr($startRecord, 9, 16-9);
        $header["shipment_number"] = logsubstr($startRecord, 17, 23-17);
        $header["data_receiver"] = logsubstr($startRecord, 24, 31-24);

        return $header;
    }

    function readEndRecord($header, $endRecord) {
        $header["transaction_count"] = logsubstr($endRecord, 9, 16-9);
        $header["record_count"] = logsubstr($endRecord, 17, 24-17);
        $header["sum_amount"] = logsubstr($endRecord, 25, 41-25);
        $header["settlement_date"] = logsubstr($endRecord, 42, 47-42);
    }

    function makeTransactions($records) {
        $result = array();

        $trans = NULL;
        foreach ($records as $one) {
            $recordType = logsubstr($one, 7, 8-7);
            $transnr = logsubstr($one, 9, 15-9);

            if($recordType < 30 || $recordType > 32) {
                continue;
            }

            
            if(!array_key_exists($transnr, $result)) {
                $result[$transnr] = array();
            }
            
            $trans = &$result[$transnr];
             
            if($recordType == "30") {
                $this->fillAmountPost1($trans, $one);
            } else if($recordType == "31") {
                $this->fillAmountPost2($trans, $one);
            } else if($recordType == "32") {
                $this->fillAmountPost3($trans, $one);
            }
        }

        
        return array_values($result);
    }

    function fillAmountPost1(&$trans, $one) {
        $trans["transaction_type"] = logsubstr($one, 5, 6-5);
        $trans["transaction_number"] =logsubstr($one, 9, 15-9);
        $trans["settlement_date"] = logsubstr($one, 16, 21-16);
        $trans["central_id"] = logsubstr($one, 22, 23-22);
        $trans["day_code"] = logsubstr($one, 24, 25-24);
        $trans["part_payment_number"] = logsubstr($one, 26, 1);
        $trans["item_no"] = logsubstr($one, 27, 31-27);
        $trans["sign"] = logsubstr($one, 32, 1);
        $trans["amount"] = logsubstr($one, 33, 49-33);
        $trans["kid"] = logsubstr($one, 50, 74-50);
        $trans["card_issuer"] = logsubstr($one, 75, 76-75);
    }

    function fillAmountPost2(&$trans, $one) {
        $trans["form_number"] = logsubstr($one, 16, 25-16);
        $trans["agreement_id"] = logsubstr($one, 26, 34-26);
        $trans["assignment_date"] = logsubstr($one, 42, 47-42);
        $trans["debet_account"] = logsubstr($one, 48, 58-48);
    }

    function fillAmountPost3(&$trans, $one) {
        $trans["free_text_message"] = logsubstr($one, 16, 55-16);
    }

    function parseDataFile($fileinfo) {
        $matches = array();
        preg_match_all("/(NY.{78})/", $fileinfo, &$matches);

        $records = $matches[0];

        $res = array();

        $res["header"] = $this->readHeader(array_shift($records));

        $this->readEndRecord(&$res["header"], array_pop($records));

        $res["transactions"] = $this->makeTransactions($records);

        return $res;
    }

}

?>