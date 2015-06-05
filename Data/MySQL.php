<?php
/**
 * MySQL
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
namespace UKBankModulus\Data
{
    class MySQL implements ISource
    {
        private $_dbHandler;

        public function __construct($host, $user, $password, $dbName, $port = 3306) {
            $this->_dbHandler = new \MySQLi($host, $user, $password, $dbName, $port);    
        }

        public function getModulusMethods($sortCode) {
            $query = "SELECT *
                      FROM sort_code_range
                      WHERE start <= $sortCode
                      AND end >= $sortCode";

            $results = $this->_dbHandler->query($query);
            $methods = array();
            if($results) {
                if($results->num_rows > 0 ) {
                    while($row = $results->fetch_assoc()) {
                        $className = "\\UKBankModulus\\Method\\" . ucfirst(strtolower($row['mod_check']));
                        $method = new $className($sortCode);
                        foreach($row as $key=>$value) {
                            if(strlen($key) == 1) {
                                $method->assignWeight($key, $value);
                            }   
                        }
                        if($row['ex'] != 0) {
                            $method->withExceptionRule($row['ex']);
                        }
                        $methods[] = $method;
                    }
                }
            }   
            return $methods;
        }

        public function getSortCodeReplacement($sortCode) {
            $query = "SELECT *
                      FROM sort_code_replacement_table
                      WHERE origin = $sortCode";

            $results = $this->_dbHandler->query($query);
            if($results->num_rows > 0) {
                $row = $results->fetch_assoc();
                return $row['replace'];
            }
            return false;
        }
    }
}

