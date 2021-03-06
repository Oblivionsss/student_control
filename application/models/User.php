<?php

namespace application\models;
use application\core\Model;
use application\core\View;

use application\lib\ValidationCreate;

class User extends Model 
{
    // Данные для контроллера настройки
    public function getUserInfo($login)
    {
        // $this->db - метод класса app.\lib\Db
        $result = $this->db->row("SELECT Name, Surname, Matern, DateOfBirth 
        FROM teach_id 
        INNER JOIN teach_info 
        ON teach_id.ID = teach_info.id 
        WHERE login=:login", 
        array("login" => $login));
        
        return $result;
    }


    // Добавляем новую дисциплину
    public function addDisc($change)
    {
        if(!empty($_POST)) {
            $result     = ValidationCreate::checkDate($change);

            // Если нашли ошибку в валидации, возвращаем её в контроллер
            if ($result != '') {
                return $result;
            }

            // Иначе
            else {
                // Добавляем новую дисциплину в disciplyne
                // echo"qq";
                $result = $this->db->add("INSERT INTO disciplyne(teach_id, Name, Hours) 
                VALUES (:teach_id, :name, :hours)",
                array('teach_id'  => $_SESSION['id'],
                'name'      => $_POST['NameDisc'],
                'hours'     => $_POST['CountHours']));
                
                if ($result) {
                    return "Дисциплина успешно добавлена!";
                }
                else return "Ошибка при добавлении дисциплины";
            }
        }
        else return "//";
    }

    // Добавляем новую группу
    public function addGroup($change)
    {
        if(!empty($_POST)) {
            $result     = ValidationCreate::checkDate($change);

            // Если нашли ошибку в валидации, возвращаем её в контроллер
            if ($result != '') {
                return $result;
            }

            // Иначе
            else {

                $namegr     = $_POST['nameGroups'];
                $course     = $_POST['Course'];
                $step       = $_POST['level'];
                
                // Добавляем новую группу
                // echo"qq";
                // Делаем запрос на существование группы
                $result = $this->db->row("SELECT id 
                FROM groups_id
                WHERE NameOfGrups=:namegr",
                array('namegr'  => $namegr));

                if ( !empty($result) ) {
                    return "Такая группа уже существует";
                }

                // Если группы нет такой
                // Добавляем, получаем id
                $result = $this->db->add("INSERT INTO groups_id(NameOfGrups, Course, Step) 
                VALUES (:namegr, :course, :step)",
                array('namegr'  => $namegr,
                'course'        => $course,
                'step'          => $step));
                
                // return "Ошибка, попробуйте снова";


                // Добавляем группу-список студентов с использованием в названии id
                // tablename : Имя группы = "id" + id по groups_id
                // groups_id.id является внешним ключом для tablename  
                $tablename  = "id_" . $result;
                
                $result     = $this->db->query("CREATE TABLE " . $tablename. "(
                id_students INT(11) NOT NULL,
                FOREIGN KEY (id_students) REFERENCES student_list(id) ON DELETE CASCADE )");

                return "Группа добавлена";

                // $result     = $this->db->query("CREATE TABLE " . $tablename. "(
                // date DATE, 
                // id_students INT(11) NOT NULL,
                // vars VARCHAR(70) NOT NULL,
                // FOREIGN KEY (id_students) REFERENCES groups_id(id) ON DELETE CASCADE )");

            }
        }
        else return "//";
    }


    public function addStud()
    {
        if(!empty($_POST)) {
            $result     = ValidationCreate::checkDate('stud');


            // Если нашли ошибку в валидации, возвращаем её в контроллер
            if ($result != '') {
                return $result;
            }

            
            // Добавляем студента в student_list
            // Получаем id студента
            else {
                $result = $this->db->add("INSERT INTO student_list(Name, Surname, id_group) 
                VALUES (:nameSt, :nameSn, :groups)",
                array('nameSt'  => $_POST['nameSt'],
                'nameSn'        => $_POST['nameSn'],
                'groups'        => $_POST['groups_id']));

                
                // Теперь добавляем студента в группу прототип
                // Подготавливаем переменные для запроса

                $id_stud    = $result;
                $id_groups  = $_POST['groups_id'];

                
                // Формируем название таблицы
                $tablename  = "id_" . $id_groups;
                $sql    = "INSERT INTO " . $tablename. "(id_students)
                VALUES(:id)";
                $result     = $this->db->query($sql,
                array ('id' => $id_stud));
                    
                return "Студент добавлен";
            }

        }
        return "Ошибка добавления, пожалуйста перезагрузите страницу";
    }


    public function addDiscGroup() {
        if(!empty($_POST)) {
            $result     = ValidationCreate::checkDate('discgroup');


            // Если нашли ошибку в валидации, возвращаем её в контроллер
            if ($result != '') {
                return $result;
            }


            else {

                $id_groups  = $_POST['groupselect'];
                $id_disc    = $_POST['discselect'];

                $tablename  = "id_" . $id_groups . "_" . $id_disc;
                $sql        = "SHOW TABLES LIKE '" . $tablename . "'";
                // Проверяем на существование группы
                $check     = $this->db->row($sql);
                
                if ( !empty($check) ) {
                    return "Такая группа уже существует!";
                } 

                // Создаем таблицу id_groups_id_disc
                $result     = $this->db->query("CREATE TABLE " . $tablename. "(
                id INT(11) NOT NULL AUTO_INCREMENT,
                datetime DATE NOT NULL,
                id_students INT(11) NOT NULL,
                status VARCHAR(30),
                dop_parametrs VARCHAR(50),
                PRIMARY KEY (id),
                FOREIGN KEY (id_students) REFERENCES student_list(id) ON DELETE CASCADE )");

                // Добавляем в список групп id_group_disc_teach 
                // закрепляя каждую группу за конкретным преподавателем
                $sql    = "INSERT INTO id_group_disc_teach(id_teach, id_group, id_disc)
                VALUES (:id_teach, :id_group, :id_disc)"; 

                $result = $this->db->query($sql,
                array('id_teach'    => $_SESSION['id'],
                        'id_group'          => $id_groups,
                        'id_disc'           => $id_disc));

                return "Индивидуальная группа добавлена";

            }
        }
        return "Ошибка добавления, пожалуйста перезагрузите страницу";
    }


    public function getGroupsInfo() 
    {
        $result = $this->db->row("SELECT id, NameOfGrups FROM groups_id");
        return $result;
    }

    public function getDiscInfo()
    {
        $result = $this->db->row("SELECT id, Name FROM disciplyne");
        return $result;
    }


    // Список групп 
    public function getUniqGroupsInfo()
    {
        $result = $this->db->row("SELECT DISTINCT id_group_disc_teach.id_group, groups_id.NameOfGrups  
        FROM id_group_disc_teach
        INNER JOIN groups_id
        ON id_group_disc_teach.id_group = groups_id.id
        WHERE id_group_disc_teach.id_teach = :id",
        array('id'  => $_SESSION['id']));
        
        return $result;
    }


    // Список дисципин по группе
    public function getUniqDiscInfo($id_group)
    {
        $result = $this->db->row("SELECT DISTINCT id_group_disc_teach.id_disc, disciplyne.Name  
        FROM id_group_disc_teach
        INNER JOIN disciplyne
        ON id_group_disc_teach.id_disc = disciplyne.id
        WHERE id_group_disc_teach.id_teach = :id
        AND id_group_disc_teach.id_group = :id_group",
        array('id'  => $_SESSION['id'],
                'id_group'  => $id_group));
        
        return $result;

    }


    // Добавляем расписание
    public function addRasp()
    {
        $tablename_prot = "id_" . $_POST['group_id'];
        $tablename_ind  = $tablename_prot . "_" . $_POST['disc_id'];
        
        //Добавляем расписание в id_group_disc_teach 
        $result = $this->db->query("UPDATE id_group_disc_teach
        SET dateAdd=:date, rep=:rep
        WHERE id_group=:id_group
        AND id_disc=:id_disc",
        array ('date'   => $_POST['date'],
        'rep'       => $_POST['radio'],
        'id_group'  => $_POST['group_id'],
        'id_disc'   => $_POST['disc_id']
    ));
    
    // Полуаем список id-студентов из групп-прототипов 
    // для обновления sиндивидуальных групп 
    $stud_id = $this->db->row("SELECT id_students
        FROM " . $tablename_prot);


    // Обновляем данные индивидуальных групп
    // Добавляя записи datatime * id_students

    // определяем перую дату
    $data  = $_POST['date'];

    // определяем интервал повтора занятий
    $up         = "2019-05-31";  // коонец семестра
    $repeat     = '';

        if ($_POST === 0) {
            $repeat = '+ 1 WEEK';
        }
        else $repeat = '+2 WEEK';

        // заполняем таблицу - уникальную группу данными
        while ($data <= $up) 
        {
            // Перебираем все id
            foreach ($stud_id as $key => $value) {
                $this->db->query("INSERT INTO " . $tablename_ind . "(
                datetime, id_students)
                VALUES (:date, :id)",
                array('date'  => $data,
                'id'    => $value['id_students'] ));  
            }

            // Добавляем период
            $data = date('Y-m-d', strtotime($repeat, strtotime($data)));         
        }

        // echo "<pre>";
        //     echo $value['id_students'];
        // echo "</pre>";
        
        // echo $_POST['date'];

        
        // $date = strtotime('+5 WEEK', strtotime($_POST['date']));
        // $newDate = date('Y-m-d', $date);
        // echo "<br>" . $newDate;
        // // Обновляем индивидуальные группы
        
        return "Расписание обновлено";
    }

    public function getListStud ($gr, $ds) {
        $table  = "id_" . $gr; 
        $result = $this->db->row("SELECT student_list.Name, student_list.Surname
        FROM " . $table . 
        ", student_list
        WHERE student_list.id = ". $table . ".id_students");

        return $result;
    }


    // Получаем список по студентам 
    // Данные за 5 последних записей до сегодняшего дня 
    // Данные за 5 + записей с сегодняшнего числа - если существуют  

    public function getData()
    {   
        // Подготовим данные
        $group_id   = $_GET['group_id'];
        $disc_id    = $_GET['disc_id'];

        // Получаем сегодняшнюю дату
        $today      = date("Y-m-d"); 
        
        // Получаем дату добавления предмета   
        $lowerDefault= $this->db->column("SELECT dateAdd
        FROM id_group_disc_teach
        WHERE id_teach = :id_teach
        AND id_group = :id_group
        AND id_disc  = :id_disc",
        array('id_teach'    => $_SESSION['id'],
            'id_group'      => $group_id,
            'id_disc'       => $disc_id));

        

            
        // Имя таблицы
        $tablename = "id_" . $group_id . "_" . $disc_id;
            
        // Верхняя граница по умочанию
        $up         = "2019-05-31"; 
        // Нижняя граница по умолчанию
        $lower      = $lowerDefault;
        // Верхняя граница дат фактическая  
        $uppDefault = $this->db->column("SELECT DISTINCT datetime
        FROM " . $tablename . 
        " ORDER BY datetime DESC 
        LIMIT 1");

        // Ограничения на выборку 
        $limit = 5;

        // Сортировка выборки по умолчанию в порядке убывания
        $sort = "DESC";

        // Подготовка sql-запроса
        $sql = "SELECT DISTINCT datetime 
        FROM " . $tablename . 
        " WHERE datetime between '" . $lower . 
        "' and '" . $up . 
        "' ORDER BY datetime ". $sort . 
        " LIMIT " . $limit;

        
        // Рассчет нижней выборки
        // Получаем последние даты до сегодняшнего дня
        // Если дата обращения >= даты выборки
        if ($today      >= $lower) {
            // Верхняя граница - сегодняшняя дата
            $up         = $today;
            

            // 
            $sql = "SELECT DISTINCT datetime 
            FROM " . $tablename . 
            " WHERE datetime between '" . $lower . 
            "' and '" . $up . 
            "' ORDER BY datetime ". $sort . 
            " LIMIT " . $limit;
            // 


            $databefore = $this->db->row($sql); 
            
            // echo "$lower<br>$up<br>$sort<br>$limit<br><br>";
            // Проверяем факт. строк 
            $rowBefore  = count($databefore);

            // Добавим недостающее количество данных по выборке
            // В выборку после нашей даты 
            if ($rowBefore < 5) {
                $limit  = 5 - $rowBefore;
            }
        }
        else {
            // Иначе выдаем все ближайшие 10 дат
            // $up == верхняя граница выборок
            $sort   = "ASC";
            $up     = $uppDefault;
            $limit  = 10;
            

            // 
            $sql = "SELECT DISTINCT datetime 
            FROM " . $tablename . 
            " WHERE datetime between '" . $lower . 
            "' and '" . $up . 
            "' ORDER BY datetime ". $sort . 
            " LIMIT " . $limit;
            // 
            

            return $this->db->row($sql);
        }

        
        // Теперь получаем все даты верхней границы
        // Если дата запрос <= фактической границы семестра
        if ($today  <= $uppDefault) {
            
            // Меняем метод сортировки
            $sort   = "ASC";
            // Нижняя граница - сегодняшняя дата
            $lower  = $today;
            // Верхняя граница - факт.граница сема
            $up     = $uppDefault;
            
            // echo "$lower<br>$up<br>$sort<br>$limit<br><br>";
            

            //
            $sql = "SELECT DISTINCT datetime 
            FROM " . $tablename . 
            " WHERE datetime between '" . $lower . 
            "' and '" . $up . 
            "' ORDER BY datetime ". $sort . 
            " LIMIT " . $limit;
            // 
            
            
            // Получаем даты 
            $dateafter  = $this->db->row($sql);
        }
        else {
            // Иначе выдаем последние 10 дат 
            $sort   = "DESC";
            $lower  = $lowerDefault;
            $up     = $uppDefault;


            // 
            $sql = "SELECT DISTINCT datetime 
            FROM " . $tablename . 
            " WHERE datetime between '" . $lower . 
            "' and '" . $up . 
            "' ORDER BY datetime ". $sort . 
            " LIMIT " . $limit;
            // 

            return $this->db->row($sql);             
        }
        // Объединим получившиеся данные
        $date = array_merge($databefore, $dateafter);
        
        // Сортируем в порядке возрастания
        asort($date);
        return $date;
    }
}