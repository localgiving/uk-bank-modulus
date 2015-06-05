<?php
/**
 * Checker
 * 
 * @package UKBankModulus
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

#   -----------------------------------------------------------------------    #
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
namespace UKBankModulus
{
    class Checker
    {
        private $_sortCode;
        private $_accountNumber;
        private $_dataSource;

        public function __construct($sortCode, $accountNumber) {
            $this->_sortCode = $this->_formatSortCode($sortCode);
            $this->_accountNumber = $accountNumber;

            $this->_validateAccountInformation();
        }

        private function _formatSortCode($sortCode) {
            if(strstr($sortCode, "-")) {
                return str_replace("-","", $sortCode);
            }
            return $sortCode;
        }

        private function _validateAccountInformation() {
            if(strlen($this->_sortCode) != 6 ) {
                throw new CheckerException("Invalid sort code provided. Sort code must be six digits long");
            }

            if(strlen($this->_accountNumber) != 8) {
                throw new CheckerException("Invalid account number. Account number must be eight digits long.");
            }
        }

        /**
         * Sets a valid data source for the Sort Code lookup
         * @param \UKBankModulus\Data\ISource $dataSource The valid Data source.
         * @return \UKBankModulus\Checker
         */
        public function setDataSource(Data\ISource $dataSource) {
            $this->_dataSource = $dataSource;
            return $this;
        }

        /**
         * Validates the given Sort Code and Account number
         * Note : Exception 2 & 9 Not handled as they are for European Lloyds business accounts only
         * @return bool
         */
        public function validate() {
            if(is_null($this->_dataSource)) {
                throw new CheckerException("No Data source provided");
            }
            
            $methods = $this->_dataSource->getModulusMethods($this->_sortCode);
            $replace = $this->_dataSource->getSortCodeReplacement($this->_sortCode);
            $return = false;
            $secondCheck = false;
            $continue = array(2,9,10,11,12,13);
            foreach($methods as $method) {
                $ex = $method->getExceptionRule();
                if($ex == 2) return true;
                if($secondCheck){
                    if(!in_array($ex, $continue) && !$return) {
                        break;
                    }
                }
                if($replace) {
                    $method->setSortCodeReplacement($replace);
                }
                if($method->validate($this->_accountNumber) == false) {
                    if(($ex == 13 || $ex == 11)&& $return == true) {
                        $return = true;
                    } else {
                        $return = false;
                    }
                } else { 
                    $return = true;
                }
                $secondCheck = true;
                if($method->stop) {
                    break;
                }
            }
            if(empty($methods)) {
                $return = true;
            }
            return $return;
        }
    }

    class CheckerException extends \Exception {}
}
