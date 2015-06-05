<?php
/**
 * Dblal
 * @package UKBankModulus
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

#   -----------------------------------------------------------------------    #
#                                                                              #
#    Permission is hereby granted, free of charge, to any person               #
#    obtaining a copy of this software and associated documentation            #
#    files (the "Software"), to deal in the Software without                   #
#    restriction, including without limitation the rights to use,              #
#    copy, modify, merge, publish, distribute, sublicense, and/or sell         #
#    copies of the Software, and to permit persons to whom the                 #
#    Software is furnished to do so, subject to the following                  #
#    conditions:                                                               #
#                                                                              #
#    The above copyright notice and this permission notice shall be            #
#    included in all copies or substantial portions of the Software.           #
#                                                                              #
#    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,           #
#    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES           #
#    OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND                  #
#    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT               #
#    HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,              #
#    WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING              #
#    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR             #
#    OTHER DEALINGS IN THE SOFTWARE.                                           #
# ============================================================================ # 

namespace UKBankModulus\Method
{
    class Dblal implements IMethod
    {
        private $_weights = array();
        private $_exception = 0;
        private $_sortCode;
        private $_replace = false;

        public $stop = false;

        /**
         * Constructor
         * @param string $sortCode
         */
        public function __construct($sortCode) {
            $this->_sortCode = $sortCode;
        }

        public function setSortCodeReplacement($sortCode) {
            $this->_replace = $sortCode;
        }
        /**
         * Assign the weight for each digit position as define in specification
         * @param string $key Position
         * @param int $weight Weight
         */
        public function assignWeight($key, $weight) {
            $this->_weights[$key] = $weight;
        }
        /**
         * Define any exception rule to be used
         * @param int $ruleId The rule ID to use
         */
        public function withExceptionRule($ruleId) {
            $this->_exception = $ruleId;
        }
        /**
         * Return the exception number
         * @return int
         */
        public function getExceptionRule() {
            return $this->_exception;
        }
        /**
         * Validate the given account number using the Dblal method
         * @param int $accountNumber
         * @return bool
         */
        public function validate($accountNumber) {
            $sortCode = str_split($this->_sortCode);
            if($this->_replace) {
                $sortCode = str_split($this->_replace);
            }
            $account = str_split($accountNumber);

            if($this->_exception == 3) {
                if($account[2] == "6" || $account[2] == "9") {
                    return true;
                }
            }

            if($this->_exception == 6) {
                $exceptionValues = array(4,5,6,7,8);
                if((in_array((int)$account[0], $exceptionValues)) && ($account[6] == $account[7] )) {
                    $this->stop = true;
                    return true;
                }
            }

            $weightedTotal = array();
            $weightedTotal[] = (int)$sortCode[0] * $this->_weights['u'];
            $weightedTotal[] = (int)$sortCode[1] * $this->_weights['v'];
            $weightedTotal[] = (int)$sortCode[2] * $this->_weights['w'];
            $weightedTotal[] = (int)$sortCode[3] * $this->_weights['x'];
            $weightedTotal[] = (int)$sortCode[4] * $this->_weights['y'];
            $weightedTotal[] = (int)$sortCode[5] * $this->_weights['z'];
            $weightedTotal[] = (int)$account[0] * $this->_weights['a'];
            $weightedTotal[] = (int)$account[1] * $this->_weights['b'];
            $weightedTotal[] = (int)$account[2] * $this->_weights['c'];
            $weightedTotal[] = (int)$account[3] * $this->_weights['d'];
            $weightedTotal[] = (int)$account[4] * $this->_weights['e'];
            $weightedTotal[] = (int)$account[5] * $this->_weights['f'];
            $weightedTotal[] = (int)$account[6] * $this->_weights['g'];
            $weightedTotal[] = (int)$account[7] * $this->_weights['h'];

            $total = 0;
            foreach($weightedTotal as $val) {
                if($val >= 10) {
                    $digits = str_split($val);
                    $total += $digits[0] + $digits[1];
                } else {
                    $total += $val;
                }
            }

            if($this->_exception == 1) {
                $total += 27;
            }

            $modulus = $total % 10;
            //Exception 5
            if($this->_exception == 5) {
                $remainder = 10 - $modulus;
                if(($modulus === 0 && $account[7] === "0") || ($remainder === (int)$account[7])) {
                    return true;
                } else {
                    return false;
                }
            }

            return (($modulus === 0) ? true : false);
        }
    }
}
