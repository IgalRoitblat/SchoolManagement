<?php

class Student extends Person
{

	function __construct($name, $phone, $email, $image_url)
	{
		parent::__construct($name, $phone, $email, $image_url);
	}

	public static function getAll() {
			$result = DB::getConnection()->query("
				SELECT id, name, phone, email, image_url
				FROM students");

			$result = $result->fetchAll();
			return $result;
	}

	public static function getOne($id) {
			$stmt = DB::getConnection()->prepare("
			SELECT * FROM students WHERE id = :id;");

			$stmt->bindParam(':id', $id, PDO::PARAM_STR);
			$stmt->execute();

			$result = $stmt->fetch();
			return $result;
	}

	public static function enrollment($id) {
		$stmt = DB::getConnection()->prepare("
		SELECT s.id, s.name, c.name, c.image_url FROM enrollment enr JOIN students s on s.id = enr.student_id JOIN courses c on c.id = enr.course_id WHERE enr.student_id = :id");

		$stmt->bindParam(':id', $id, PDO::PARAM_STR);
		$stmt->execute();

		$result = $stmt->fetchAll();
		return $result;
	}

	public function add() {
		$stmt = DB::getConnection()->prepare("
			INSERT INTO students (name, phone, email, image_url)
			VALUES (:name, :phone, :email, :image_url)");

		if ($stmt->errorCode()) {
			die($stmt->errorInfo()[0]);
		}

		$stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
		$stmt->bindParam(':phone', $this->photo, PDO::PARAM_STR);
		$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
		$stmt->bindParam(':image_url', $this->image_url, PDO::PARAM_STR);

		$stmt->execute();
		$row_id = DB::getConnection();

		return $row_id->lastInsertId();

	}

	public static function delete($id) {
		$stmt = DB::getConnection()->prepare("
			DELETE FROM students WHERE id = :id;");

			$stmt->bindParam(':id', $id, PDO::PARAM_STR);
			$stmt->execute();

			echo "student $id was deleted!";
	}

	public function edit($id, $courses) {
		$student_update = DB::getConnection()->prepare("
			UPDATE students SET name = :name, phone = :phone, email = :email, image_url = :image_url WHERE id = :id
			");

		$student_update->bindParam(':name', $this->name, PDO::PARAM_STR);
		$student_update->bindParam(':phone', $this->phone, PDO::PARAM_STR);
		$student_update->bindParam(':email', $this->email, PDO::PARAM_STR);
		$student_update->bindParam(':image_url', $this->image_url, PDO::PARAM_STR);
		$student_update->bindParam(':id', $id, PDO::PARAM_STR);

		if ($student_update->errorCode()) {
			die($student_update->errorInfo()[0]);
		}

		$student_update->execute();
		$student_update->fetchAll();

		$studet_courses = self::enrollment($id);

		self::clear_enrollment($id);
		self::enroll_to_class($id, $courses);
		
		return $student_update;
	}

	public static function enroll_to_class($student_id, $courses) {
		foreach ($courses as $course) {
			$stmt = DB::getConnection()->prepare("
				INSERT INTO enrollment (student_id, course_id)
				VALUES (:student_id, :course)");

			if ($stmt->errorCode()) {
				die($stmt->errorInfo()[0]);
			}

			$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
			$stmt->bindParam(':course', $course, PDO::PARAM_INT);

			$stmt->execute();
			$stmt->fetchAll();
		};
	}

	public static function clear_enrollment($student_id) {
			$stmt = DB::getConnection()->prepare("
				DELETE FROM enrollment WHERE student_id = :student_id");

			if ($stmt->errorCode()) {
				die($stmt->errorInfo()[0]);
			}

			$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

			$stmt->execute();
			$stmt->fetchAll();

		return $stmt;
	}

	public static function count() {
		$result = DB::getConnection()->query("
			SELECT id
			FROM students");

		$result = $result->rowCount();
		return $result;
	}
}