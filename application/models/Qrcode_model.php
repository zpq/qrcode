<?php

class Qrcode_model extends CI_model {

    private $table = "t_qrcode";

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get($id) {
        $sql = "select * from " . $this->table . " where id=?";
        $query = $this->db->query($sql, array($id));
        $row = $query->row();
        return $row;
    }

    public function update($data) {
        $id = $data['id'];

        $this->db->where('id', $id);
        $this->db->update($this->table, $data);

        return $this->db->affected_rows();
    }

    public function insert($data) {
		$this->db->insert($this->table, $data);
		return $this->db->affected_rows();
    }

}

?>