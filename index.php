<?php
// Composer deps
require_once('vendor/autoload.php');

// Fat-free
$f3 = Base::instance();

// Configs
require('config.php');

// Language selector
if($f3->get('GET.language')){
    setcookie('language', $f3->get('GET.language'));
    header('Location: '.$_SERVER['HTTP_REFERER']);
}

// Env vars
require('config.php');

// Fat-free vars
$f3->set('TEMP', $f3->get('AWM_TEMP_DIR'));
$f3->set('DEBUG', 0); // 0 = production; 3 = debug mode
$f3->set('CACHE', 'memcached=localhost:11211');
$f3->set('TZ', 'America/Bahia');
$f3->set('LOCALES', 'dict/');
$f3->set('LANGUAGE', $_COOKIE['language']);
$f3->set('FALLBACK', 'en-US');
$f3->set('AUTOLOAD', 'src/');

// Email
$f3->set('smtp', 
    new SMTP(
        $f3->get('AWM_EMAIL_SERVER'), 
        $f3->get('AWM_EMAIL_PORT'), 
        $f3->get('AWM_EMAIL_SCHEMA'),  
        $f3->get('AWM_EMAIL_LOGIN'), 
        $f3->get('AWM_EMAIL_PASSWORD')
    )
); 

// Database
$f3->set('db', new DB\SQL(
    'mysql:host=localhost;port=3306;dbname='.
    $f3->get('AWM_DB_DBNAME'),
    $f3->get('AWM_DB_LOGIN'),
    $f3->get('AWM_DB_PASSWORD')
));
 
// SESSION
ini_set('session.gc_maxlifetime', 5800);
new \DB\SQL\Session($f3->get('db'));
 
// ======================
// App
// ======================

$f3->route('GET /',
    function($f3) {
        $f3->set('page','home');
        
        // Get all institutions verified from database
        $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Institutions'));
        $records = $f3->get('sql')->find(array('status = ?', 'verified'));
        
        // Create pins for each institution and put in the map
        $institutions = new Institutions();
        $f3->set('pins', $institutions->pins($records));
        
        echo \Template::instance()->render('templates/home.html');
    }
);

$f3->route('GET /add',
    function($f3) {
        $f3->set('page','add');
 
        echo \Template::instance()->render('templates/home.html');
    }
);

$f3->route('POST /proc-add',
    function($f3) {
        $f3->set('page','proc-add');
       
        $recaptcha = new \ReCaptcha\ReCaptcha($f3->get('AWM_PRIVATE_KEY_RECAPCHA')); // https://www.google.com/recaptcha/
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            // Recaptcha verified! 
            
             $iduser = $f3->get('db')->exec('SELECT id,name, email FROM Users WHERE email = ?', $f3->get('POST.contributoremail'));

            // If the email is not in Users table...
            if($f3->get('db')->count() == 0){
                
                // Add user
                $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Users'));
                $f3->get('sql')->name =                 $f3->get('POST.contributor');
                $f3->get('sql')->email =                $f3->get('POST.contributoremail');
                $f3->get('sql')->hash =                 'recover';
                $f3->get('sql')->save();
                
                $iduser = $f3->get('sql')->get('_id');
                
                // Add Institution
                $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Institutions'));
                $f3->get('sql')->latitude =             $f3->get('POST.latitude');
                $f3->get('sql')->longitude =            $f3->get('POST.longitude');
                $f3->get('sql')->name =                 $f3->get('POST.name');
                $f3->get('sql')->identifier =           $f3->get('POST.identifier');
                $f3->get('sql')->address =              $f3->get('POST.address');
                $f3->get('sql')->city =                 $f3->get('POST.city');
                $f3->get('sql')->district =             $f3->get('POST.district');
                $f3->get('sql')->country =              $f3->get('POST.country');
                $f3->get('sql')->url =                  $f3->get('POST.url');
                $f3->get('sql')->email =                $f3->get('POST.email');
                $f3->get('sql')->collaborator_name =    $f3->get('POST.collaborator_name');
                $f3->get('sql')->collaborator_email =   $f3->get('POST.collaborator_email');
                $f3->get('sql')->status =               'waiting';
                $f3->get('sql')->save();
                
                $idinstitution = $f3->get('sql')->get('_id');
                
                // Add Users_Institutions
                $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Users_Institutions'));
                $f3->get('sql')->iduser =             $iduser[0]['id'];
                $f3->get('sql')->idinstitution =      $idinstitution;
                $f3->get('sql')->save();
            } else {
                // Do not add user
                              
                // Add Institution
                $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Institutions'));
                $f3->get('sql')->latitude =             $f3->get('POST.latitude');
                $f3->get('sql')->longitude =            $f3->get('POST.longitude');
                $f3->get('sql')->name =                 $f3->get('POST.name');
                $f3->get('sql')->identifier =           $f3->get('POST.identifier');
                $f3->get('sql')->address =              $f3->get('POST.address');
                $f3->get('sql')->city =                 $f3->get('POST.city');
                $f3->get('sql')->district =             $f3->get('POST.district');
                $f3->get('sql')->country =              $f3->get('POST.country');
                $f3->get('sql')->url =                  $f3->get('POST.url');
                $f3->get('sql')->email =                $f3->get('POST.email');
                $f3->get('sql')->collaborator_name =    $f3->get('POST.collaborator_name');
                $f3->get('sql')->collaborator_email =   $f3->get('POST.collaborator_email');
                $f3->get('sql')->status =               'waiting';
                $f3->get('sql')->save();
                
                $idinstitution = $f3->get('sql')->get('_id');
                
                // Add Users_Institutions
                $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Users_Institutions'));
                $f3->get('sql')->iduser =             $iduser[0]['id'];
                $f3->get('sql')->idinstitution =      $idinstitution;
                $f3->get('sql')->save();
                
            }

            // Send the congratulations for add
            $f3->get('smtp')->set('To', '"' . $iduser[0]['name'] . '" <' . $iduser[0]['email'] . '>');
            $f3->get('smtp')->set('From', '"Archives World Map" <' . $f3->get('AWM_EMAIL_ADDRESS') . '>');
            $f3->get('smtp')->set('Subject', '[Archives World Map] Thank you for your submission');
            $f3->get('smtp')->set('Errors-to', '<ricardo@feudo.org>');
            $f3->get('smtp')->set('content-type','text/html;charset=utf-8');
            $f3->set('message', 'Hi' . ' ' . $iduser[0]['name'] . '!' .
                '<p>Thank you that new institution data you sent to us! Our team will check it and add ' .
                'into the <strong>Archives World Map</strong> as fast we can.</p>' . 
                '<p>You sent us data about: <strong>' . $f3->get('POST.name') . '</strong></p>' .
                '<p>Best regards,</p>' .
                '<p>Ricardo Sodré Andrade' . 
                '<br>admin@archivesmap.org' .
                '<br><a href="https://www.archivesmap.org">https://www.archivesmap.org</a></p>'
            );
            $f3->set('statusemailsending', $f3->get('smtp')->send($f3->get('message')));

            if($f3->get('statusemailsending') == TRUE){
                $f3->set('emailerror', 'noerror');
            } else {
                $f3->set('emailerror', 'sendingfailed');
            }	
        } else {
            // Recaptcha fail
        }            
    
        $f3->reroute('/add-done');
    }
);

$f3->route('GET /add-done',
    function($f3) {
        $f3->set('page','add-done');

        echo \Template::instance()->render('templates/home.html');
   }
);

$f3->route('GET /add-fail',
    function($f3) {
        $f3->set('page','add-fail');

        echo \Template::instance()->render('templates/home.html');
   }
);

$f3->route('GET /stats',
    function($f3) {
        $f3->set('page','stats');

        // Quantidade de instituicoes verificadas
        $f3->set('res_stats', $f3->get('db')->exec('SELECT id FROM Institutions WHERE status = "verified"')); 
        $f3->set('qty_institutions', strval((count($f3->get('res_stats')))));

        $f3->set('res_stats', $f3->get('db')->exec(
            'SELECT country, count(country) AS visits FROM Institutions ' .
            'WHERE status = "verified" ' .
            'GROUP BY country ORDER BY visits DESC LIMIT 10')
        ); 
        
        $f3->set('ranking_countries', $f3->get('res_stats'));

        echo \Template::instance()->render('templates/home.html');
    }
);

$f3->route('GET /about',
    function($f3) {
        $f3->set('page','about');

        echo \Template::instance()->render('templates/home.html');
    }
);

$f3->route('GET /login',
    function($f3) {
        $f3->set('page','login');

        echo \Template::instance()->render('templates/login.html');
    }
);

$f3->route('POST /checklogin',
    function($f3) {
        $f3->set('page','checklogin');
        
        $recaptcha = new \ReCaptcha\ReCaptcha($f3->get('AWM_PRIVATE_KEY_RECAPCHA')); // https://www.google.com/recaptcha/
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
        // Recaptcha verified!      
            $f3->set('mapUsers', new DB\SQL\Mapper($f3->get('db'), 'Users'));
            $crypt = \Bcrypt::instance();
            $f3->set('auth', new \Auth($f3->get('mapUsers'), array('id'=>'email', 'pw'=>'hash')));

            if(!$f3->get('SESSION.logged')){ 
                if($f3->get('auth')->login($f3->get('POST.inputEmail'), $crypt->hash($f3->get('POST.inputPassword'), $f3->get('AWM_PASSWORDGEN_SALT'))) == FALSE){
                    $f3->clear('SESSION.logged');
                    session_commit();
                    $f3->reroute('/');
                } else {
                    $f3->set('SESSION.logged', 'yes');
                    $f3->set('SESSION.id', $f3->get('db')->exec('SELECT id FROM Users WHERE email = ?', $f3->get('POST.inputEmail')));
                    $f3->set('SESSION.name', $f3->get('db')->exec('SELECT name FROM Users WHERE email = ?', $f3->get('POST.inputEmail')));
                    $f3->set('SESSION.email', $f3->get('POST.inputEmail'));
                    $f3->set('SESSION.privilege', $f3->get('db')->exec('SELECT privilege FROM Users WHERE email = ?', $f3->get('POST.inputEmail')));
                    
                    $f3->reroute('/dashboard?qty=10&since=0');
                }
            } else {
                $f3->reroute('/dashboard?qty=10&since=0');
            }
        } else {
            // Recaptcha fail
            $f3->reroute('/login');
        }
    }
);

$f3->route('GET /logout',
    function($f3) {
        $f3->set('page','logout');

        $f3->clear('SESSION.logged');
        $f3->clear('SESSION.id');
        $f3->clear('SESSION.name');
        $f3->clear('SESSION.email');
        $f3->clear('SESSION.privilege');
        session_destroy();

        $f3->reroute('/');
    }
);

$f3->route('GET /register',
    function($f3) {
        $f3->set('page','register');

        echo \Template::instance()->render('templates/login.html');
    }
);

$f3->route('POST /proc-register',
    function($f3) {
        $f3->set('page','proc-register');
        
        $recaptcha = new \ReCaptcha\ReCaptcha($f3->get('AWM_PRIVATE_KEY_RECAPCHA')); // https://www.google.com/recaptcha/
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            // Recaptcha verified! 
            
            // Check if email exist
            $audit = \Audit::instance();
            if($audit->email($f3->get('POST.email'), FALSE)){
                $f3->reroute($_SERVER['HTTP_REFERER']);
            }
            
            $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Users'));

            // Verify if user already exist
            if($f3->get('sql')->count(array('email LIKE ?', '%'.$f3->get('POST.inputEmail').'%')) > 0){
                echo 'alert("This email was registered before. You can recover your password instead.");';
                $f3->reroute('/login');
            }
            
            // Gerar nova senha
            $crypt = \Bcrypt::instance();
            $characters = '#$%&@=!123456789abcdefghjkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            
            $password = $crypt->hash($randomString, $f3->get('AWM_PASSWORDGEN_SALT'));
            
            $f3->get('sql')->name =                 $f3->get('POST.inputName');
            $f3->get('sql')->email =                $f3->get('POST.inputEmail');
            $f3->get('sql')->country =              $f3->get('POST.country');
            $f3->get('sql')->hash =                 $password;
            $f3->get('sql')->save();
            
            // Send the new password
            $f3->get('smtp')->set('To', '"' . $f3->get('POST.inputName') . '" <' . $f3->get('POST.inputEmail') . '>');
            $f3->get('smtp')->set('From', '"Archives World Map" <' . $f3->get('AWM_EMAIL_ADDRESS') . '>');
            $f3->get('smtp')->set('Subject', '[Archives World Map] Your account was created');
            $f3->get('smtp')->set('Errors-to', '<ricardo@feudo.org>');
            $f3->get('smtp')->set('content-type','text/html;charset=utf-8');
            $f3->set('message', 'Hi' . ' ' . $f3->get('POST.inputName') . '!' .
                '<p>A new account in <strong>Archives World Map</strong> was created.</p>' . 
                '<p>This message was generated by our platform.</p>' .
                '<p>Your login is: ' . $f3->get('POST.inputEmail') .
                '<br>Your password is: ' . $randomString . '</p>' .
                '<p>Best regards,</p>' .
                '<p>Ricardo Sodré Andrade' . 
                '<br>admin@archivesmap.org' .
                '<br><a href="https://www.archivesmap.org">https://www.archivesmap.org</a></p>'
            );
            $f3->get('smtp')->send($f3->get('message'));
            
            echo \Template::instance()->render('templates/login.html');
        } else {
            // Recaptcha fail
            $f3->reroute('/register');
        }
    }
);

$f3->route('GET /recover-password',
    function($f3) {
        $f3->set('page','recover-password');
    
        echo \Template::instance()->render('templates/login.html');
    }
);

$f3->route('POST /proc-recover-password',
    function($f3) {
        $f3->set('page','proc-recover-password');
    
        $recaptcha = new \ReCaptcha\ReCaptcha($f3->get('AWM_PRIVATE_KEY_RECAPCHA')); // https://www.google.com/recaptcha/
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            // Recaptcha verified! 
            
    
            $f3->set('res_recover', $f3->get('db')->exec('SELECT * FROM Users WHERE email = ?', $f3->get('POST.inputEmail')));
            if($f3->get('db')->count() != 0){

                // Gerar nova senha
                $crypt = \Bcrypt::instance();
                $characters = '#$%&@=!123456789abcdefghjkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 10; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $f3->get('db')->exec('UPDATE Users SET hash = "' . $crypt->hash($randomString, $f3->get('AWM_PASSWORDGEN_SALT')) . 
                    '" WHERE email = ?', $f3->get('POST.inputEmail'));

                // Send the new password by email
                $f3->get('smtp')->set('To', '"' . $f3->get('res_recover')[0]['name'] . '" <' . $f3->get('res_recover')[0]['email'] . '>');
                $f3->get('smtp')->set('From', '"Archives World Map" <' . $f3->get('AWM_EMAIL_ADDRESS') . '>');
                $f3->get('smtp')->set('Subject', '[Archives World Map] Your password was changed');
                $f3->get('smtp')->set('Errors-to', '<ricardo@feudo.org>');
                $f3->get('smtp')->set('content-type','text/html;charset=utf-8');
                $f3->set('message', 'Hi' . ' ' . $f3->get('res_recover')[0]['name'] . '!' .
                    '<p>Your password at <strong>Archives World Map</strong> was changed.</p>' . 
                    '<p>This message was generated by our platform.</p>' .
                    '<p>Your new password is: ' . $randomString . '</p>' .
                    '<p>Best regards,</p>' .
                    '<p>Ricardo Sodré Andrade' . 
                    '<br>admin@archivesmap.org' .
                    '<br><a href="https://www.archivesmap.org">https://www.archivesmap.org</a></p>'
                );
                $f3->set('statusemailsending', $f3->get('smtp')->send($f3->get('message')));

                if($f3->get('statusemailsending') == TRUE){
                    $f3->set('emailerror', 'noerror');
                } else {
                    $f3->set('emailerror', 'sendingfailed');
                }			
          
            } else {
                $f3->set('emailerror', 'noemailregistered');
            }
        
            echo \Template::instance()->render('templates/login.html');
        } else {
            // Recaptcha fail
            $f3->reroute('/recover-password');
        }
    }
);

$f3->route('GET /dashboard',
    function($f3) {
        $f3->set('page','dashboard');
    
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
     
        // List Intitutions
        $f3->set('res_institutions', $f3->get('db')->exec(
            'SELECT Institutions.id, Institutions.name, Institutions.country, Users_Institutions.id AS iduserinst FROM Institutions ' .
            'LEFT JOIN Users_Institutions ON Users_Institutions.idinstitution = Institutions.id ' .
            'AND Users_Institutions.iduser = ' . $f3->get('SESSION.id')[0]['id'] . ' ' .
            'LIMIT :limit OFFSET :offset',
            array(
                ':limit'=>(int)$f3->get('GET.qty'),
                ':offset'=>(int)$f3->get('GET.since')
            )
        ));
        
        // Count
        $f3->get('db')->exec('SELECT id FROM Institutions');
        $f3->set('count', $f3->get('db')->count());
        
        // Count content from each institutions
        
        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('GET /myinstitutions',
    function($f3) {
        $f3->set('page','myinstitutions');

        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
               
        // Get "My Institutions"
        $f3->set('res_myinstitutions', $f3->get('db')->exec(
            'SELECT Institutions.id, Institutions.name, Institutions.country, Users.id as iduser FROM Users_Institutions ' .
            'INNER JOIN Institutions ON Users_Institutions.idinstitution = Institutions.id ' .
            'INNER JOIN Users ON Users_Institutions.iduser = Users.id ' .
            'WHERE iduser = ' . $f3->get('SESSION.id')[0]['id'] . ' ' .
            'LIMIT :limit OFFSET :offset',
            array(
                ':limit'=>(int)$f3->get('GET.qty'),
                ':offset'=>(int)$f3->get('GET.since')
            )
        ));
        
        // Count
        $f3->get('db')->exec(
            'SELECT Institutions.id, Institutions.name, Institutions.country, Users.id as iduser FROM Users_Institutions ' .
            'INNER JOIN Institutions ON Users_Institutions.idinstitution = Institutions.id ' .
            'INNER JOIN Users ON Users_Institutions.iduser = Users.id ' .
            'WHERE iduser = ' . $f3->get('SESSION.id')[0]['id']
        );
        $f3->set('count', $f3->get('db')->count());

        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('GET /institution/@id',
    function($f3) {
        $f3->set('page','institution');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
        
        $f3->set('res_institution', $f3->get('db')->exec(
            'SELECT Institutions.*, Users.id AS iduser FROM Institutions ' .
            'INNER JOIN Users ON Users.email = Institutions.collaborator_email ' .
            'WHERE Institutions.id = ?', $f3->get('PARAMS.id')
        )); 

        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('GET /mapper/@id',
    function($f3) {
        $f3->set('page','mapper');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/login'); }
        
        $f3->set('res_user', $f3->get('db')->exec(
            'SELECT Users.id, Users.name, Profiles.* FROM Users ' .
            'LEFT JOIN Profiles ON Profiles.iduser = Users.id ' .
            'WHERE Users.id = ?', $f3->get('PARAMS.id')
        )); 

        $f3->set('awm_score', Score::calc($f3->get('db'), $f3->get('PARAMS.id')));
        
        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('GET /myprofile',
    function($f3) {
        $f3->set('page','myprofile');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
        
        $f3->set('res_user', $f3->get('db')->exec(
            'SELECT Users.id, Users.name, Users.email, Profiles.* FROM Users ' .
            'LEFT JOIN Profiles ON Profiles.iduser = Users.id ' .
            'WHERE Users.id = ' . $f3->get('SESSION.id')[0]['id']
        )); 

        $f3->set('awm_score', Score::calc($f3->get('db'), $f3->get('SESSION.id')[0]['id']));
        
        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('GET /edit-myprofile',
    function($f3) {
        $f3->set('page','edit-myprofile');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
        
        $f3->set('res_user', $f3->get('db')->exec(
            'SELECT Users.id, Users.name, Users.email, Profiles.* FROM Users ' .
            'LEFT JOIN Profiles ON Profiles.iduser = Users.id ' .
            'WHERE Users.id = ' . $f3->get('SESSION.id')[0]['id']
        ));
        
        $f3->set('selected_country', '<option value="' . $f3->get('res_user')[0]['country'] . '">'.$f3->get('res_user')[0]['country'].'</option>');
        
        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('POST /proc-edit-myprofile',
    function($f3) {
        $f3->set('page','proc-edit-myprofile');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
        
        /* Check if email exist
        $audit = \Audit::instance();
        if($audit->email($f3->get('POST.email'), FALSE)){
            $f3->reroute('/myprofile');
        }*/
        
        // Update Table Profiles
        $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Profiles'));
        $f3->get('sql')->load(array('iduser = ?', $f3->get('SESSION.id')[0]['id']));
        
        $f3->get('sql')->genre =            $f3->get('POST.genre');
        $f3->get('sql')->url =              $f3->get('POST.url');
        $f3->get('sql')->institution =      $f3->get('POST.institution');
        $f3->get('sql')->education =        $f3->get('POST.education');
        $f3->get('sql')->country =          $f3->get('POST.country');
        $f3->get('sql')->district =         $f3->get('POST.district');
        $f3->get('sql')->city =             $f3->get('POST.city');
        $f3->get('sql')->iduser =           $f3->get('SESSION.id')[0]['id'];
        $f3->get('sql')->save();
        
        // Update Table Users
        $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Users'));
        $f3->get('sql')->load(array('id = ?', $f3->get('SESSION.id')[0]['id']));
        
        $f3->get('sql')->name =            $f3->get('POST.name');
        $f3->get('sql')->email =           $f3->get('POST.email'); // TODO Audit email
        $f3->get('sql')->country =          $f3->get('POST.country');
        $f3->get('sql')->save();
                
        $f3->reroute('/myprofile');
    }
);

$f3->route('GET /addtomyinstitutions/@id',
    function($f3) {
        $f3->set('page','addtomyinstitutions');
    
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
        
        // Verify if is already in myinstitution
        $f3->get('db')->exec('SELECT id FROM Users_Institutions WHERE iduser = :iduser AND idinstitution = :idinstitution',
            array(
                ':iduser'=>$f3->get('SESSION.id')[0]['id'],
                ':idinstitution'=>(int)$f3->get('PARAMS.id')
            )
        );
        if($f3->get('db')->count() == 0){
            // Verified, to add
            $f3->get('db')->exec('INSERT INTO Users_Institutions(iduser, idinstitution) VALUES (' .
                $f3->get('SESSION.id')[0]['id'] . ', ?)', $f3->get('PARAMS.id')
            );
        }
    
        $f3->reroute($_SERVER['HTTP_REFERER']);
    }
);

$f3->route('GET /removefrommyinstitutions/@id',
    function($f3) {
        $f3->set('page','removefrommyinstitutions');
    
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/'); }
        
        // Verificar se não já está fora antes de remover
        $res = $f3->get('db')->exec('SELECT id FROM Users_Institutions WHERE iduser = ' . 
            $f3->get('SESSION.id')[0]['id'] . ' ' .
            'AND idinstitution = ?', $f3->get('PARAMS.id')
        );
        
        // Remover
        if($f3->get('db')->count() > 0){
            $f3->get('db')->exec('DELETE FROM Users_Institutions WHERE id = ' . $res[0]['id']);
        }
        $f3->reroute($_SERVER['HTTP_REFERER']);
    }
);

$f3->route('GET /info/@id',
    function($f3) {
        $f3->set('page','info');
               
        $f3->set('res_institution', $f3->get('db')->exec(
            'SELECT Institutions.*, Users.id AS iduser, Users.email AS useremail FROM Institutions ' .
            'LEFT JOIN Users ON Users.email = Institutions.collaborator_email ' .
            'WHERE Institutions.id = ?', $f3->get('PARAMS.id'))); 

        echo \Template::instance()->render('templates/home.html');
    }
);

$f3->route('GET /q',
    function($f3) {
        $f3->set('page','q');   
        
        // Get Institutions
        $f3->set('res_institutions', $f3->get('db')->exec(
                'SELECT id, name, country FROM Institutions ' .
                'WHERE name LIKE :search LIMIT :qty OFFSET :since', 
            array(
                ':search'=>'%'.$f3->get('GET.search').'%',
                ':qty'=>(int)$f3->get('GET.qty'),
                ':since'=>(int)$f3->get('GET.since')
            )
        )); 
        
        // Count
        $f3->get('db')->exec(
            'SELECT id, name, country FROM Institutions ' .
            'WHERE name LIKE ?', '%'.$f3->get('GET.search').'%'
        );
        $f3->set('count', $f3->get('db')->count());
    
        echo \Template::instance()->render('templates/home.html');
    }
);

$f3->route('GET /bycountry/@country',
    function($f3) {
        $f3->set('page','bycountry');   
                 
        // Get Institutions
        $f3->set('res_institutions', $f3->get('db')->exec(
                'SELECT id, name, country FROM Institutions ' .
                'WHERE country = :country LIMIT :qty OFFSET :since', 
            array(
                ':country'=>$f3->get('PARAMS.country'),
                ':qty'=>(int)$f3->get('GET.qty'),
                ':since'=>(int)$f3->get('GET.since')
            )
        )); 
        
        // Count
        $f3->get('db')->exec(
            'SELECT id FROM Institutions ' .
            'WHERE country = ?', $f3->get('PARAMS.country')
        );
        $f3->set('count', $f3->get('db')->count());
        
        if($f3->get('SESSION.logged') == 'yes'){
            echo \Template::instance()->render('templates/dashboard.html');
        } else {
            echo \Template::instance()->render('templates/home.html');
        }
    }
);

$f3->route('GET /content/@id',
    function($f3) {
        $f3->set('page','content');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/login'); }
        
        // Get Content
        $f3->set('res_content', $f3->get('db')->exec(
                'SELECT ' . 
                'Institutions.name, Content_Institutions.idinstitution, Content_Institutions.id AS idci, Content.* ' .
                'FROM Content_Institutions ' .
                'INNER JOIN Content ON Content.id = Content_Institutions.idcontent ' .
                'INNER JOIN Institutions ON Institutions.id = Content_Institutions.idinstitution ' .
                'LIMIT :qty OFFSET :since',
            array(
                ':qty'=>(int)$f3->get('GET.qty'),
                ':since'=>(int)$f3->get('GET.since')
            )
        )); 

        // Count
        $f3->get('db')->exec(
            'SELECT Content_Institutions.idinstitution, Content_Institutions.id AS idci, Content.* FROM Content_Institutions ' .
            'INNER JOIN Content ON Content.id = Content_Institutions.idcontent ' .
            'INNER JOIN Institutions ON Institutions.id = Content_Institutions.idinstitution'
        ); 
        $f3->set('count', $f3->get('db')->count());
        
        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('GET /community/@id',
    function($f3) {
        $f3->set('page','community');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/login'); }
        
        // Get Topics
        $f3->set('res_community', $f3->get('db')->exec(
                'SELECT Community.id, Community.title, Community.datetime, Users.id as iduser, Users.name, Institutions.name as instname FROM Community ' .
                'INNER JOIN Users ON Users.id = Community.iduser ' .
                'INNER JOIN Institutions ON Institutions.id = Community.idinstitution ' .
                'WHERE idinstitution = :institution AND Community.parent_id IS NULL ' .
                'LIMIT :qty OFFSET :since',
            array(
                ':institution'=>$f3->get('PARAMS.id'),
                ':qty'=>(int)$f3->get('GET.qty'),
                ':since'=>(int)$f3->get('GET.since')
            )
        ));

        // Count
        $f3->get('db')->exec(
                'SELECT Community.id, Community.datetime, Users.name, Institutions.name as instname FROM Community ' .
                'INNER JOIN Users ON Users.id = Community.iduser ' .
                'INNER JOIN Institutions ON Institutions.id = Community.idinstitution ' .
                'WHERE idinstitution = ? AND Community.parent_id IS NULL', $f3->get('PARAMS.id')
        ); 
        $f3->set('count', $f3->get('db')->count());
        
        echo \Template::instance()->render('templates/dashboard.html');
    }
);

$f3->route('GET /community_post/@id',
    function($f3) {
        $f3->set('page','community_post');
        
        // Verify if is logged
        if(!$f3->get('SESSION.logged')){ $f3->reroute('/login'); }
        
        // Get Topics
        $f3->set('res_community_post', $f3->get('db')->exec(
                'SELECT Users.id AS iduser, Users.id AS iduser, Users.name, Institutions.name AS instname, Institutions.id AS instid, Community.* FROM Community ' .
                'INNER JOIN Users ON Users.id = Community.iduser ' .
                'INNER JOIN Institutions ON Institutions.id = Community.idinstitution ' .
                'WHERE Community.id = :post ',
            array(
                ':post'=>$f3->get('PARAMS.id')
            )
        ));
        
        // GET REPLIES from TOPIC
        $f3->set('res_community_post_replies', $f3->get('db')->exec(
            'SELECT Users.id AS iduser, Users.id AS iduser, Users.name, Institutions.name AS instname, Institutions.id AS instid, Community.* FROM Community ' .
            'INNER JOIN Users ON Users.id = Community.iduser ' .
            'INNER JOIN Institutions ON Institutions.id = Community.idinstitution ' .
            'WHERE Community.parent_id = :post ' .
            'LIMIT :qty OFFSET :since',
            array(
                ':post'=>$f3->get('PARAMS.id'),
                ':qty'=>(int)$f3->get('GET.qty'),
                ':since'=>(int)$f3->get('GET.since')
            )
        ));
        
        // Count
        $f3->get('db')->exec(
            'SELECT Users.id AS iduser, Users.id AS iduser, Users.name, Institutions.name AS instname, Institutions.id AS instid, Community.* FROM Community ' .
            'INNER JOIN Users ON Users.id = Community.iduser ' .
            'INNER JOIN Institutions ON Institutions.id = Community.idinstitution ' .
            'WHERE Community.parent_id = ?', $f3->get('PARAMS.id')
        ); 
        $f3->set('count', $f3->get('db')->count());
        
        echo \Template::instance()->render('templates/dashboard.html');
    }
);

/*
$f3->route('GET /migrarbd',
    function($f3) {
        $f3->set('page','migrarbd');
        
        $f3->set('oldmapdb', new \DB\SQL('sqlite:/var/www/ArchivesMap.db'));
        $f3->set('sqlite', new DB\SQL\Mapper($f3->get('oldmapdb'), 'arquivos'));
        $old = $f3->get('sqlite')->find(array('status = ?', 'verified'));
        $a = 0;
        foreach($old as $oldmap){
            $a++;
            $f3->set('sql', new DB\SQL\Mapper($f3->get('db'), 'Institutions'));

            $f3->get('sql')->latitude =             $oldmap['latitude'];
            $f3->get('sql')->longitude =            $oldmap['longitude'];
            $f3->get('sql')->name =                 $oldmap['nome'];
            $f3->get('sql')->identifier =           $oldmap['identificador'];
            $f3->get('sql')->address =              $oldmap['logradouro'];
            $f3->get('sql')->city =                 $oldmap['cidade'];
            $f3->get('sql')->district =             $oldmap['estado'];
            $f3->get('sql')->country =              $oldmap['pais'];
            $f3->get('sql')->url =                  $oldmap['url'];
            $f3->get('sql')->email =                $oldmap['email'];
            $f3->get('sql')->collaborator_name =    $oldmap['contributor'];
            $f3->get('sql')->collaborator_email =   $oldmap['contributoremail'];
            $f3->get('sql')->importedfrom =         $oldmap['imported'];
            $f3->get('sql')->status =               $oldmap['status'];
            $f3->get('sql')->save();
            echo $oldmap['id'] . ' - ' . $oldmap['nome'] . '<br>';
        }
        
        echo 'Migrado';
    }
);


$f3->route('GET /migrausers',
    function($f3) {
        $f3->set('page','migrausers');
        
        $f3->set('dbi',
            new DB\SQL(
                'mysql:host=localhost;port=3306;dbname='.
                $f3->get('AWM_DB_DBNAME'),
                $f3->get('AWM_DB_LOGIN'),
                $f3->get('AWM_DB_PASSWORD')
            )
        );
        $f3->set('dba',
            new DB\SQL(
                'mysql:host=localhost;port=3306;dbname='.
                $f3->get('AWM_DB_DBNAME'),
                $f3->get('AWM_DB_LOGIN'),
                $f3->get('AWM_DB_PASSWORD')
            )
        );
        
        $f3->set('sql', new DB\SQL\Mapper($f3->get('dbi'), 'Institutions'));
        $old = $f3->get('sql')->find(array('collaborator_email LIKE ?', '%@%'));
        $a = 0;
        foreach($old as $oldmap){
            
            $f3->get('db')->exec('SELECT id FROM Users WHERE email = "' . $oldmap['collaborator_email'] . '"');
            
            if($f3->get('db')->count() == 0){
                $f3->set('sql', new DB\SQL\Mapper($f3->get('dba'), 'Users'));

                $f3->get('sql')->name =             $oldmap['collaborator_name'];
                $f3->get('sql')->email =            $oldmap['collaborator_email'];
                $f3->get('sql')->hash =             'recover';
                $f3->get('sql')->save();
            }
        }
        
        echo 'Migrado';
    }
);


$f3->route('GET /migra_rel_user_inst',
    function($f3) {
        $f3->set('page','migra_rel_user_inst');
        
        $users = $f3->get('db')->exec('SELECT id,email FROM Users');
        foreach($users as $user){
            
            $f3->set('dba', new DB\SQL(
                    'mysql:host=localhost;port=3306;dbname='.
                    $f3->get('AWM_DB_DBNAME'),
                    $f3->get('AWM_DB_LOGIN'),
                    $f3->get('AWM_DB_PASSWORD')
                )
            );
            $instituicoes = $f3->get('dba')->exec('SELECT id FROM Institutions WHERE collaborator_email = "' . $user['email'].'"');
            foreach($instituicoes as $inst){
                $f3->set('dbi',new DB\SQL(
                        'mysql:host=localhost;port=3306;dbname='.
                        $f3->get('AWM_DB_DBNAME'),
                        $f3->get('AWM_DB_LOGIN'),
                        $f3->get('AWM_DB_PASSWORD')
                    )
                );
                //echo $user['id'] . ', ' . $inst['id'] . '<br>';
                $f3->get('dbi')->exec('INSERT INTO Users_Institutions(iduser,idinstitution) VALUES('.$user['id'].', '.$inst['id'].')');
            }    
        }
    }
);
*/

$f3->run();