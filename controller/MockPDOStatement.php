<?php
class MockPDOStatement {
    private $data = [];
    private $position = 0;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function fetch($fetch_style = PDO::FETCH_ASSOC) {
        if ($this->position >= count($this->data)) {
            return false;
        }
        
        return $this->data[$this->position++];
    }
    
    public function rowCount() {
        return count($this->data);
    }
}
?> 