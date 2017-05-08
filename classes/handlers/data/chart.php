<?php

class DataHandlerChart implements OpenPADataHandlerInterface
{
    public function __construct( array $Params )
    {

    }

    public function getData()
    {
        $data = array(
          array('',1,3),
          array(1,5,7),
          array(2,3,9),
        );

        $delimiter = ',';
        $enclosure = '"';

        header('Content-Type: text/plain; charset=utf-8');
        $output = fopen('php://output', 'w');
        foreach($data as $index => $row){
            $this->fputcsv($output, $row, $delimiter, $enclosure, $index == count($data)-1);
        }
        eZExecution::cleanExit();
    }

    private function fputcsv(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"', $last = false) {
        $str = '';
        $escape_char = '\\';
        foreach ($fields as $value) {
            if (strpos($value, $delimiter) !== false ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i=0;$i<$len;$i++) {
                    if ($value[$i] == $escape_char) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                    $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2.$delimiter;
            } else {
                $str .= $value.$delimiter;
            }
        }
        $str = substr($str,0,-1);
        if (!$last) {
            $str .= "\n";
        }
        return fwrite($handle, $str);
    }
}
