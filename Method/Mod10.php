<?php
/**
 * Mod10
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
    class Mod10 implements IMethod
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
         * Validate the given account number using the Mod10 method
         * @param int $accountNumber
         * @return bool
         */
        public function validate($accountNumber) {
            if($this->_exception == 8) {
                $this->_sortCode = "090126";
            }

            $sortCode = str_split($this->_sortCode);
            $account = str_split($accountNumber);

            $weightedTotal = 0;
            $weightedTotal += $sortCode[0] * $this->_weights['u'];
            $weightedTotal += $sortCode[1] * $this->_weights['v'];
            $weightedTotal += $sortCode[2] * $this->_weights['w'];
            $weightedTotal += $sortCode[3] * $this->_weights['x'];
            $weightedTotal += $sortCode[4] * $this->_weights['y'];
            $weightedTotal += $sortCode[5] * $this->_weights['z'];
            $weightedTotal += $account[0] * $this->_weights['a'];
            $weightedTotal += $account[1] * $this->_weights['b'];
            $weightedTotal += $account[2] * $this->_weights['c'];
            $weightedTotal += $account[3] * $this->_weights['d'];
            $weightedTotal += $account[4] * $this->_weights['e'];
            $weightedTotal += $account[5] * $this->_weights['f'];
            $weightedTotal += $account[6] * $this->_weights['g'];
            $weightedTotal += $account[7] * $this->_weights['h'];

            $modulus = $weightedTotal % 10;

            return (($modulus === 0) ? true : false);
        }
    }
}
