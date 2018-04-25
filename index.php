<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
include 'DB/DB.php';
include 'Classes/Person.php';
include 'Classes/Administrator.php';
include 'Classes/Student.php';
include 'Classes/Course.php';

$settings = [
    'settings' => [
        'displayErrorDetails' => true
    ]
];

$app = new \Slim\App($settings);

$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('views');
    
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$app->get('/', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'login.html');
});

$app->post('/validate', function (Request $request, Response $response) {
    session_start();
    $body = $request->getParsedBody();

    if (Administrator::validate($body['username'], $body['password'])) {
        $_SESSION['username'] = $body['username'];
        $_SESSION['password'] = $body['password'];
        return $response->withRedirect("/school");
    } else {
       return $response->withRedirect("/"); 
    };
});

$app->get('/school', function (Request $request, Response $response, array $args) {
    session_start();
    $student_count = Student::count();
    $course_count = Course::count();

    if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
        $admin = Administrator::getOne($_SESSION['username']);

        return $this->view->render($response, 'main.html', [
            'admin' => $admin,
            'student_count' => $student_count,
            'course_count' => $course_count
        ]);
    } else return $response->withRedirect("/");

});

$app->get('/students', function (Request $request, Response $response, array $args) {
    session_start();
    $students = Student::getAll();
    $student_count = Student::count();
    $course_count = Course::count();

    if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
        $admin = Administrator::getOne($_SESSION['username']);

        return $this->view->render($response, 'items-view.html', [
            'students' => $students,
            'admin' => $admin,
            'student_count' => $student_count,
            'course_count' => $course_count
        ]);
    } else return $response->withRedirect("/");

});

$app->get('/courses', function (Request $request, Response $response, array $args) {
    session_start();
    $courses = Course::getAll();
    $student_count = Student::count();
    $course_count = Course::count();

    if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
        $admin = Administrator::getOne($_SESSION['username']);

        return $this->view->render($response, 'items-view.html', [
            'courses' => $courses,
            'admin' => $admin,
            'student_count' => $student_count,
            'course_count' => $course_count
        ]);
    } else return $response->withRedirect("/");

});

$app->get('/administrator', function (Request $request, Response $response) {
    session_start();
    $admins = Administrator::getAll();
    $admin_count = Administrator::count();
    $type = 'Administrator';

    if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
        $admin = Administrator::getOne($_SESSION['username']);

        return $this->view->render($response, 'items-view.html', [
            'admins' => $admins,
            'admin' => $admin,
            'admin_count' => $admin_count,
            'type' => $type
        ]);
    } else return $response->withRedirect("/");

});

$app->post('/{type}/add', function (Request $request, Response $response, array $args) {
    $type = $args['type'];
    $body = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    $file = $files['image'];
    $uploadFileName = md5($file->getClientFilename());
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/images/" . $uploadFileName;
    $file->moveTo($targetPath);

    switch ($type) {
        case 'Administrator':
            $role = $body['role'];
            $admin = new Administrator($body['name'], $body['phone'], $body['email'], $uploadFileName, $body['role'], $body['password']);
            $last_id = $admin->add();
            $role_assign = Administrator::role_assign($last_id, $role);
            break;
        case 'Course':
            $students_list = $body['check_list'];
            $course = new Course($body['name'], $body['description'], $uploadFileName);
            $last_id = $course->add();
            $enrollment = Course::enroll_students($last_id, $students_list);
            break;
        case 'Student':
            $course_list = $body['check_list'];
            $student = new Student($body['name'], $body['phone'], $body['email'], $uploadFileName);
            $last_id = $student->add();
            $enrollment = Student::enroll_to_class($last_id, $course_list);
            break;
        
        default:
            
            break;
    };
    return $response->withRedirect("/$type/$last_id");
});

$app->get('/{type}/insert', function (Request $request, Response $response, array $args) {
    session_start();
    $type = $args['type'];

    switch ($type) {
        case 'Administrator':
            $admins = Administrator::getAll();
            $roles = Administrator::allRoles();

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username'], $_SESSION['password']);
                return $this->view->render($response, 'new.html', [
                    'admins' => $admins,
                    'admin' => $admin,
                    'roles' => $roles,
                    'type' => $type
                 ]);
            } else return $response->withRedirect("/"); 
            break;
        case 'Student':
            $courses_available = Course::getAll();
            $students = Student::getAll();

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username'], $_SESSION['password']);
                return $this->view->render($response, 'new.html', [
                    'courses_available' => $courses_available,
                    'students' => $students,
                    'admin' => $admin,
                    'type' => $type
                ]);
            } else return $response->withRedirect("/"); 
            break;  
        case 'Course':
            $courses = Course::getAll();
            $students_available = Student::getAll();

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username'], $_SESSION['password']);
                return $this->view->render($response, 'new.html', [
                    'courses' => $courses,
                    'students_available' => $students_available,
                    'admin' => $admin,
                    'type' => $type
                ]);
            } else return $response->withRedirect("/"); 
            break;              
        default:
            return $response->withRedirect("/");
        break;
    }
});


$app->post('/{type}/delete/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $type = $args['type'];
    $body = $request->getParsedBody();
    $id_to_delete = $body['id_to_delete'];

    switch ($type) {
        case 'Administrator':
            Administrator::delete($id_to_delete);
            return $response->withRedirect("/administrator");
            break;

        case 'Student':
            Student::delete($id_to_delete);
            return $response->withRedirect("/school");
            break;

        case 'Course':
            Course::delete($id_to_delete);
            return $response->withRedirect("/school");
            break;

        default:
            break;
    }
});

$app->get('/{type}/update/{id}', function (Request $request, Response $response, array $args) {
    session_start();
    $type = $args['type'];
    $id = $args['id'];

    switch ($type) {
        case 'Administrator':
            $admins = Administrator::getAll();
            $roles = Administrator::allRoles();
            $admin_info = Administrator::role_distribution($id);

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username']);
                return $this->view->render($response, 'info_edit.html', [
                    'admins' => $admins,
                    'roles' => $roles,
                    'admin_info' => $admin_info,
                    'admin' => $admin,
                    'old_pass' => $_SESSION['password'],
                    'type' => $type
                ]);
            } else return $response->withRedirect("/");
            break;
        case 'Student':
            $students = Student::getAll();
            $courses_available = Course::getAll();
            $student = Student::getOne($id);
            $student_courses = Student::enrollment($id);

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username']);
                return $this->view->render($response, 'info_edit.html', [
                    'students' => $students,
                    'student' => $student,
                    'courses_available' => $courses_available,
                    'student_courses' => $student_courses,
                    'admin' => $admin,
                    'type' => $type
                ]);
            } else return $response->withRedirect("/");
            break;
        case 'Course':
            $courses = Course::getAll();
            $course = Course::getOne($id);
            $students_available = Student::getAll();
            $course_students = Course::enrollment($id);

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username']);
                return $this->view->render($response, 'info_edit.html', [
                    'courses' => $courses,
                    'course' => $course,
                    'course_students' => $course_students,
                    'students_available' => $students_available,
                    'admin' => $admin,
                    'type' => $type
                ]);
            } else return $response->withRedirect("/");
            break;
        default:
            break;
    }

});

$app->post('/{type}/updated/{id}', function (Request $request, Response $response, array $args) {
    $type = $args['type'];
    $id = $args['id'];
    $body = $request->getParsedBody();
    $files = $request->getUploadedFiles();

    switch ($type) {
        case 'Administrator':
            if (!$body['role']) {
                $role = Administrator::getRole($id)['role'];
            } else {
                $role = $body['role'];
            }
            if (!$files) {
              Administrator::edit($id, $body['name'], $body['phone'], $body['email'], $body['prev_image'], $role);  
            } else {
                $file = $files['image'];
                $uploadFileName = md5($file->getClientFilename());
                $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/images/" . $uploadFileName . ".jpg";
                $file->moveTo($targetPath);
                Administrator::edit($id ,$body['name'], $body['phone'], $body['email'], $uploadFileName . ".jpg", $role);
            };
            if ($role == 1) {
                session_destroy();
                return $response->withRedirect("/");
            } else return $response->withRedirect("/$type/$id");
            break;
        case 'Student':
            if (!$files) {
              $student = new Student($body['name'], $body['phone'], $body['email'], $body['prev_image']);  
            } else {
                $file = $files['image'];
                $uploadFileName = md5($file->getClientFilename());
                $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/images/" . $uploadFileName . ".jpg";
                $file->moveTo($targetPath);
                $student = new Student($body['name'], $body['phone'], $body['email'], $uploadFileName . ".jpg");
            };

            $course_list = $body['check_list'];
            $student = $student->edit($id, $course_list);    
            
            return $response->withRedirect("/school");
            break;            
        case 'Course':
            if (!$files) {
              $course = new Course($body['name'], $body['description'], $body['prev_image']);  
            } else {
                $file = $files['image'];
                $uploadFileName = md5($file->getClientFilename());
                $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/images/" . $uploadFileName . ".jpg";
                $file->moveTo($targetPath);
                $course = new Course($body['name'], $body['description'], $uploadFileName . ".jpg");
            };

            $student_list = $body['check_list'];  
            $course = $course->edit($id, $student_list);
            
            return $response->withRedirect("/school");
            break;        
        default:
            break;
    }

});

$app->get('/{type}/{id}', function (Request $request, Response $response, array $args) {
    session_start();
    $id = $args['id'];
    $type = $args['type'];

    switch ($type) {
        case 'Administrator':
            $admins = Administrator::getAll();
            $admin_info = Administrator::role_distribution($id);

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username']);
                return $this->view->render($response, 'info.html', [
                    'admins' => $admins,
                    'admin_info' => $admin_info,
                    'admin' => $admin,
                    'type' => $type
                ]);
            } else return $response->withRedirect("/");
            break;
        case 'Student':
            $students = Student::getAll();
            $student = Student::getOne($id);
            $student_courses = Student::enrollment($id);

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username']);
                return $this->view->render($response, 'info.html', [
                    'students' => $students,
                    'student' => $student,
                    'student_courses' => $student_courses,
                    'admin' => $admin,
                    'type' => $type
                ]);
            } else return $response->withRedirect("/");
            break; 
        case 'Course':
            $courses = Course::getAll();
            $course = Course::getOne($id);
            $course_students = Course::enrollment($id);

            if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
                $admin = Administrator::getOne($_SESSION['username']);
                return $this->view->render($response, 'info.html', [
                    'courses' => $courses,
                    'course' => $course,
                    'course_students' => $course_students,
                    'admin' => $admin,
                    'type' => $type
                ]);
            } else return $response->withRedirect("/"); 
            break;       
        default:
            return $response->withRedirect("/");
            break;
    }
});

$app->run();