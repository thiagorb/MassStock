<?php

class Barcala_CsvAdapter_Model_Request_Interpreter_Csv implements Mage_Api2_Model_Request_Interpreter_Interface
{
    /**
     * Parse Request body into array of items
     *
     * @param string $body  Posted content from request
     * @return array|null   Return NULL if content is invalid
     * @throws Exception|Mage_Api2_Exception
     */
    public function interpret($body)
    {
        if (!is_string($body)) {
            throw new Exception(sprintf('Invalid data type "%s". String expected.', gettype($body)));
        }

        try {
            $decoded = [];
            $delimiter = $this->_getDelimiter();
            $enclosure = $this->_getEnclosure();
            $escape = $this->_getEscape();
            $lines = preg_split('/\r?\n/', $body);

            if ($this->_hasHeader()) {
                $headers = str_getcsv(array_shift($lines), $delimiter, $enclosure, $escape);
            }

            foreach ($lines as $line) {
                $row = str_getcsv(array_shift($lines), $delimiter, $enclosure, $escape);

                if (!$this->_hasHeader()) {
                    $decoded[] = $row;
                    continue;
                }

                $item = [];
                foreach ($headers as $index => $header) {
                    if (isset($row[$index]) && $row[$index] !== '') {
                        $item[$headers[$index]] = $row[$index];
                    }
                }
                $decoded[] = $item;
            }

            return $decoded;
        } catch (Exception $e)         {
            throw new Mage_Api2_Exception('Decoding error.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    protected function _getDelimiter()
    {
        return Mage::app()->getRequest()->getHeader('Content-Delimiter') ?: ',';
    }

    protected function _getEnclosure()
    {
        return Mage::app()->getRequest()->getHeader('Content-Enclosure') ?: '"';
    }

    protected function _getEscape()
    {
        return Mage::app()->getRequest()->getHeader('Content-Escape') ?: '\\';
    }

    protected function _hasHeader()
    {
        return Mage::app()->getRequest()->getHeader('Content-Header') !== 'false';
    }
}