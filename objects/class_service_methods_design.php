<?php

class cleanto_service_methods_design
{

    public $id;
    public $design;
    public $service_methods_id;
    public $table_name = "ct_service_methods_design";
    public $conn;
    /* to get design type to show in front end */
    public function get_service_methods_design($id)
    {
        $this->service_methods_id = (int)$id;
        if ($this->service_methods_id <= 0) {
            return '';
        }
        $query = "select `design` from `" . $this->table_name . "` where `service_methods_id`='" . $this->service_methods_id . "'";
        $result = mysqli_query($this->conn, $query);
        $value = $result ? mysqli_fetch_row($result) : null;
        $design = isset($value[0]) ? (int)$value[0] : 0;
        /* Cup24 / synced services often miss a design row — default to design 3 (button units) */
        if ($design <= 0) {
            $design = 3;
            @mysqli_query($this->conn, "INSERT INTO `" . $this->table_name . "` (`id`,`service_methods_id`,`design`)
                VALUES (NULL, " . $this->service_methods_id . ", " . $design . ")");
        }
        return (string)$design;
    }
}
