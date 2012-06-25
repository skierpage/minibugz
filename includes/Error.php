<?php 
class Error {
    private $errs = array();
    private $validationErrs = array();

    /**
     * @param $errStr string : an error message.
     */
    public function err( $errStr ) {
        $this->errs[] = $errStr;
    }

    /**
     * @param $validationErr array : a hash containing field with problem, and str message.
     */
    public function validationErr( $validationErr ) {
        $this->validationErrs[] = $validationErr;
    }

    /**
     * renders errors.
     * @return string: HTML representation of errors.
     */
    public function renderHTML() {
        $outStr = '';
        foreach ($this->errs as $str) {
            $outStr .= "<div>$str</div>\n";
        }
        if ( count( $this->validationErrs) > 0 ) {
            $outStr .= "<div>Fix the following errors with your input</div>\n";
            $outStr .= '<ul>';
            foreach ($this->validationErrs as $validationErr ) {
                $outStr .= '<li><a href="#' . $validationErr['field'] . '">'
                    . $validationErr['field'] . '</a> '
                    . $validationErr['errStr']
                    . "</li>\n";
            }
            $outStr .= '</ul>';
        }
        return $outStr;
    }

}
