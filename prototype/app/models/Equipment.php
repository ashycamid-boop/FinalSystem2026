<?php
class Equipment {
    private $conn;
    private $table_name = "equipment";

    public $id;
    public $office_division;
    public $equipment_type;
    public $year_acquired;
    public $shelf_life;
    public $brand;
    public $model;
    public $processor;
    public $ram_size;
    public $gpu;
    public $range_category;
    public $os_version;
    public $office_productivity;
    public $endpoint_protection;
    public $computer_name;
    public $serial_number;
    public $property_number;
    public $accountable_person;
    public $accountable_sex;
    public $accountable_employment;
    public $actual_user;
    public $actual_user_sex;
    public $actual_user_employment;
    public $nature_of_work;
    public $status;
    public $remarks;
    public $qr_code_path;
    public $created_by;
    public $updated_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create equipment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET office_division=:office_division,
                    equipment_type=:equipment_type,
                    year_acquired=:year_acquired,
                    shelf_life=:shelf_life,
                    brand=:brand,
                    model=:model,
                    processor=:processor,
                    ram_size=:ram_size,
                    gpu=:gpu,
                    range_category=:range_category,
                    os_version=:os_version,
                    office_productivity=:office_productivity,
                    endpoint_protection=:endpoint_protection,
                    computer_name=:computer_name,
                    serial_number=:serial_number,
                    property_number=:property_number,
                    accountable_person=:accountable_person,
                    accountable_sex=:accountable_sex,
                    accountable_employment=:accountable_employment,
                    actual_user=:actual_user,
                    actual_user_sex=:actual_user_sex,
                    actual_user_employment=:actual_user_employment,
                    nature_of_work=:nature_of_work,
                    status=:status,
                    remarks=:remarks,
                    qr_code_path=:qr_code_path,
                    created_by=:created_by";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":office_division", $this->office_division);
        $stmt->bindParam(":equipment_type", $this->equipment_type);
        $stmt->bindParam(":year_acquired", $this->year_acquired);
        $stmt->bindParam(":shelf_life", $this->shelf_life);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":processor", $this->processor);
        $stmt->bindParam(":ram_size", $this->ram_size);
        $stmt->bindParam(":gpu", $this->gpu);
        $stmt->bindParam(":range_category", $this->range_category);
        $stmt->bindParam(":os_version", $this->os_version);
        $stmt->bindParam(":office_productivity", $this->office_productivity);
        $stmt->bindParam(":endpoint_protection", $this->endpoint_protection);
        $stmt->bindParam(":computer_name", $this->computer_name);
        $stmt->bindParam(":serial_number", $this->serial_number);
        $stmt->bindParam(":property_number", $this->property_number);
        $stmt->bindParam(":accountable_person", $this->accountable_person);
        $stmt->bindParam(":accountable_sex", $this->accountable_sex);
        $stmt->bindParam(":accountable_employment", $this->accountable_employment);
        $stmt->bindParam(":actual_user", $this->actual_user);
        $stmt->bindParam(":actual_user_sex", $this->actual_user_sex);
        $stmt->bindParam(":actual_user_employment", $this->actual_user_employment);
        $stmt->bindParam(":nature_of_work", $this->nature_of_work);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":remarks", $this->remarks);
        $stmt->bindParam(":qr_code_path", $this->qr_code_path);
        $stmt->bindParam(":created_by", $this->created_by);

        if($stmt->execute()) {
            return true;
        }
        // Log detailed error for debugging
        try {
            $err = $stmt->errorInfo();
            $log = "[".date('Y-m-d H:i:s')."] create failed: ".json_encode($err)."\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/equipment_create_error.log', $log, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // ignore
        }
        return false;
    }

    // Read all equipment
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single equipment
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->office_division = $row['office_division'];
            $this->equipment_type = $row['equipment_type'];
            $this->year_acquired = $row['year_acquired'];
            $this->shelf_life = $row['shelf_life'];
            $this->brand = $row['brand'];
            $this->model = $row['model'];
            $this->processor = $row['processor'];
            $this->ram_size = $row['ram_size'];
            $this->gpu = $row['gpu'];
            $this->range_category = $row['range_category'];
            $this->os_version = $row['os_version'];
            $this->office_productivity = $row['office_productivity'];
            $this->endpoint_protection = $row['endpoint_protection'];
            $this->computer_name = $row['computer_name'];
            $this->serial_number = $row['serial_number'];
            $this->property_number = $row['property_number'];
            $this->accountable_person = $row['accountable_person'];
            $this->accountable_sex = $row['accountable_sex'];
            $this->accountable_employment = $row['accountable_employment'];
            $this->actual_user = $row['actual_user'];
            $this->actual_user_sex = $row['actual_user_sex'];
            $this->actual_user_employment = $row['actual_user_employment'];
            $this->nature_of_work = $row['nature_of_work'];
            $this->status = $row['status'];
            $this->remarks = $row['remarks'];
            $this->qr_code_path = $row['qr_code_path'];
            return true;
        }
        return false;
    }

    // Update equipment
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET office_division=:office_division,
                    equipment_type=:equipment_type,
                    year_acquired=:year_acquired,
                    shelf_life=:shelf_life,
                    brand=:brand,
                    model=:model,
                    processor=:processor,
                    ram_size=:ram_size,
                    gpu=:gpu,
                    range_category=:range_category,
                    os_version=:os_version,
                    office_productivity=:office_productivity,
                    endpoint_protection=:endpoint_protection,
                    computer_name=:computer_name,
                    serial_number=:serial_number,
                    property_number=:property_number,
                    accountable_person=:accountable_person,
                    accountable_sex=:accountable_sex,
                    accountable_employment=:accountable_employment,
                    actual_user=:actual_user,
                    actual_user_sex=:actual_user_sex,
                    actual_user_employment=:actual_user_employment,
                    nature_of_work=:nature_of_work,
                    status=:status,
                    remarks=:remarks,
                    updated_by=:updated_by
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":office_division", $this->office_division);
        $stmt->bindParam(":equipment_type", $this->equipment_type);
        $stmt->bindParam(":year_acquired", $this->year_acquired);
        $stmt->bindParam(":shelf_life", $this->shelf_life);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":processor", $this->processor);
        $stmt->bindParam(":ram_size", $this->ram_size);
        $stmt->bindParam(":gpu", $this->gpu);
        $stmt->bindParam(":range_category", $this->range_category);
        $stmt->bindParam(":os_version", $this->os_version);
        $stmt->bindParam(":office_productivity", $this->office_productivity);
        $stmt->bindParam(":endpoint_protection", $this->endpoint_protection);
        $stmt->bindParam(":computer_name", $this->computer_name);
        $stmt->bindParam(":serial_number", $this->serial_number);
        $stmt->bindParam(":property_number", $this->property_number);
        $stmt->bindParam(":accountable_person", $this->accountable_person);
        $stmt->bindParam(":accountable_sex", $this->accountable_sex);
        $stmt->bindParam(":accountable_employment", $this->accountable_employment);
        $stmt->bindParam(":actual_user", $this->actual_user);
        $stmt->bindParam(":actual_user_sex", $this->actual_user_sex);
        $stmt->bindParam(":actual_user_employment", $this->actual_user_employment);
        $stmt->bindParam(":nature_of_work", $this->nature_of_work);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":remarks", $this->remarks);
        $stmt->bindParam(":updated_by", $this->updated_by);
        $stmt->bindParam(":id", $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            // collect error info
            $err = $stmt->errorInfo();
            $log = "[".date('Y-m-d H:i:s')."] update failed (id={$this->id}): ".json_encode($err)."\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/equipment_update_error.log', $log, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            $log = "[".date('Y-m-d H:i:s')."] update exception (id={$this->id}): " . $e->getMessage() . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/equipment_update_error.log', $log, FILE_APPEND | LOCK_EX);
        }
        return false;
    }

    // Delete equipment
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
