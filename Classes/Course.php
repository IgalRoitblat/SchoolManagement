<?php

class Course
{
	private $name;
	private $description;
	private $image_url;

	
	function __construct($name, $description, $image_url)
	{
		$this->name = $name;
		$this->description = $description;
		$this->image_url = $image_url;
	}

	public static function getAll() {
			$result = DB::getConnection()->query("
				SELECT id, name, description, image_url
				FROM courses");

			$result = $result->fetchAll();
			return $result;
	}

	public static function getOne($id) {
			$stmt = DB::getConnection()->prepare("
			SELECT * FROM courses WHERE id = :id;");

			$stmt->bindParam(':id', $id, PDO::PARAM_STR);
			$stmt->execute();

			$result = $stmt->fetch();
			return $result;
	}

	public static function enrollment($id) {
		$stmt = DB::getConnection()->prepare("
		SELECT c.id, c.name, s.name, s.image_url FROM enrollment enr JOIN students s on s.id = enr.student_id JOIN courses c on c.id = enr.course_id WHERE enr.course_id = :id");

		$stmt->bindParam(':id', $id, PDO::PARAM_STR);
		$stmt->execute();

		$result = $stmt->fetchAll();
		return $result;
	}

	public function add() {
		$stmt = DB::getConnection()->prepare("
			INSERT INTO courses (name, description, image_url)
			VALUES (:name, :description, :image_url)");

		if ($stmt->errorCode()) {
			die($stmt->errorInfo()[0]);
		}

		$stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
		$stmt->bindParam(':description', $this->description, PDO::PARAM_STR);
		$stmt->bindParam(':image_url', $this->image_url, PDO::PARAM_STR);

		$stmt->execute();
		$row_id = DB::getConnection();

		return $row_id->lastInsertId();

	}

	public static function delete($id) {
		$stmt = DB::getConnection()->prepare("
			DELETE FROM courses WHERE id = :id;");

			$stmt->bindParam(':id', $id, PDO::PARAM_STR);
			$stmt->execute();

			echo "student $id was deleted!";
	}

	public function edit($id, $students) {
		$course_update = DB::getConnection()->prepare("
			UPDATE courses SET name = :name, description = :description, image_url = :image_url WHERE id = :id
			");

		$course_update->bindParam(':name', $this->name, PDO::PARAM_STR);
		$course_update->bindParam(':description', $this->description, PDO::PARAM_STR);
		$course_update->bindParam(':image_url', $this->image_url, PDO::PARAM_STR);
		$course_update->bindParam(':id', $id, PDO::PARAM_STR);

		if ($course_update->errorCode()) {
			die($course_update->errorInfo()[0]);
		}

		$course_update->execute();
		$course_update->fetchAll();

		$course_students = self::enrollment($id);

		self::clear_enrollment($id);
		self::enroll_students($id, $students);
		
		return $course_update;
	}

	public static function enroll_students($course_id, $students) {
		foreach ($students as $student) {
			$stmt = DB::getConnection()->prepare("
				INSERT INTO enrollment (student_id, course_id)
				VALUES (:student ,:course_id)");

			if ($stmt->errorCode()) {
				die($stmt->errorInfo()[0]);
			}

			$stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
			$stmt->bindParam(':student', $student, PDO::PARAM_INT);

			$stmt->execute();
			$stmt->fetchAll();
		};
	}

	public static function clear_enrollment($course_id) {
			$stmt = DB::getConnection()->prepare("
				DELETE FROM enrollment WHERE course_id = :course_id");

			if ($stmt->errorCode()) {
				die($stmt->errorInfo()[0]);
			}

			$stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);

			$stmt->execute();
			$stmt->fetchAll();

		return $stmt;
	}

	public static function count() {
		$result = DB::getConnection()->query("
			SELECT id
			FROM courses");

		$result = $result->rowCount();
		return $result;
	}
}