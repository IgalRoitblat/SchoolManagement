<?php

class Administrator extends Person
{
	private $role;
	private $password;
	
	function __construct($name, $phone, $email, $image_url, $role, $password)
	{
		parent::__construct($name, $phone, $email, $image_url);
		$this->role = $role;
		$this->password = password_hash($password, PASSWORD_DEFAULT);
	}

	public static function validate($username ,$password) {
		// $stmt = DB::getConnection()->prepare("
		// SELECT * FROM admins WHERE name = :username AND password = :password");

		$stmt = DB::getConnection()->prepare("
		SELECT * FROM admins WHERE name = :username");

		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		// $stmt->bindParam(':password', $password, PDO::PARAM_STR);
		$stmt->execute();

		$result = $stmt->fetch();

		$row_id = DB::getConnection();

		if (password_verify($password, $result['password'])) {
			return true;
		} else return false;
	}

	public static function getOne($username) {
		$stmt = DB::getConnection()->prepare("
		SELECT * FROM admins WHERE name = :username");

		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		// $stmt->bindParam(':password', $password, PDO::PARAM_STR);
		$stmt->execute();

		$result = $stmt->fetch();
		return $result;
	}

	public static function getAll() {
			$result = DB::getConnection()->query("
				SELECT * FROM admins");

			$result = $result->fetchAll();
			return $result;
	}

	public static function role_distribution($id) {
		$stmt = DB::getConnection()->prepare("
		SELECT a.id, a.name, r.name AS role_name,r.id AS role_id, a.image_url, a.phone, a.email FROM role_assign ra 
		JOIN admins a on a.id = ra.admin_id 
		JOIN roles r on r.id = ra.role_id 
		WHERE ra.admin_id = :id");

		$stmt->bindParam(':id', $id, PDO::PARAM_STR);
		$stmt->execute();

		$result = $stmt->fetch();
		return $result;
	}

	public function add() {
		$stmt = DB::getConnection()->prepare("
			INSERT INTO admins (name, phone, email, image_url, role, password)
			VALUES (:name, :phone, :email, :image_url, :role, :password)");

		if ($stmt->errorCode()) {
			die($stmt->errorInfo()[0]);
		}

		$stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
		$stmt->bindParam(':phone', $this->phone, PDO::PARAM_STR);
		$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
		$stmt->bindParam(':image_url', $this->image_url, PDO::PARAM_STR);
		$stmt->bindParam(':role', $this->role, PDO::PARAM_STR);
		$stmt->bindParam(':password', $this->password, PDO::PARAM_STR);

		$stmt->execute();
		$row_id = DB::getConnection();

		return $row_id->lastInsertId();

	}

	public static function role_assign($admin_id, $role_id) {
			$stmt = DB::getConnection()->prepare("
				INSERT INTO role_assign (admin_id, role_id)
				VALUES (:admin_id, :role_id)");

			if ($stmt->errorCode()) {
				die($stmt->errorInfo()[0]);
			}

			$stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
			$stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);

			$stmt->execute();
			$stmt->fetchAll();
	}

	public static function allRoles() {
		$result = DB::getConnection()->query("
			SELECT * FROM roles");

		$result = $result->fetchAll();
		return $result;
	}

	public static function getRole($id) {
		$stmt = DB::getConnection()->prepare("
		SELECT * FROM admins WHERE id = :id");

		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();

		$result = $stmt->fetch();
		return $result;
	}

	public static function edit($id, $name, $phone, $email, $image_url, $role) {

		$admin_update = DB::getConnection()->prepare("
			UPDATE admins SET name = :name, phone = :phone, email = :email, image_url = :image_url, role = :role WHERE id = :id
			");

		$admin_update->bindParam(':name', $name, PDO::PARAM_STR);
		$admin_update->bindParam(':phone', $phone, PDO::PARAM_STR);
		$admin_update->bindParam(':email', $email, PDO::PARAM_STR);
		$admin_update->bindParam(':image_url', $image_url, PDO::PARAM_STR);
		$admin_update->bindParam(':role', $role, PDO::PARAM_STR);
		$admin_update->bindParam(':id', $id, PDO::PARAM_STR);

		if ($admin_update->errorCode()) {
			die($admin_update->errorInfo()[0]);
		}

		$admin_update->execute();
		$admin_update->fetchAll();
		
		return $admin_update;
	}

	public static function delete($id) {
		$stmt = DB::getConnection()->prepare("
			DELETE FROM admins WHERE id = :id;");

			$stmt->bindParam(':id', $id, PDO::PARAM_STR);
			$stmt->execute();

			echo "student $id was deleted!";
	}

	public static function count() {
		$result = DB::getConnection()->query("
			SELECT id
			FROM admins");

		$result = $result->rowCount();
		return $result;
	}
}