<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\helpers\Html;

use app\models\ContactForm;
use app\models\LoginForm;
use app\models\Schools;
use app\models\SignupForm; 
use app\models\StudentLookup;
use app\models\Students;
use app\models\Transactions;
use app\models\User;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\Security;
use yii\data\Pagination;
use yii\mail\MailerInterface;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly MailerInterface $mailer,
        private readonly Security $security,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

   
       
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'only' => [
                    'bursar', 'register-student', 'edit-student', 'delete-student', 'mark-no-show', 
                    'export-students', 'export-receipts', 'term-rollover','students-directory',
                    'teacher-grading', 'submit-marks', 'dos-review', 'seal-marks', 'print-reports',
                    'logout', 'signup',
                ],
                'rules' => [
                    //  RULE 1: Super Admin Permissions
                    [
                        'actions' => ['super-admin', 'create-school', 'edit-school'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'SUPER_ADMIN';
                        }
                    ],
                    //  RULE 2: Bursar Accounting Permissions
                    [
                        'actions' => ['school-admin', 'signup', 'bursar', 'dos-review', 'seal-marks', 'print-reports', 'manage-assignments', 'delete-assignment', 'register-student', 'edit-student', 'delete-student', 'mark-no-show', 'export-students', 'export-receipts', 'term-rollover'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'SCHOOL_ADMIN';
                        }
                    ],
                    //  RULE 3: Legacy Bursar Accounting (Keep intact)
                    [
                        'actions' => ['bursar', 'register-student', 'edit-student', 'delete-student', 'mark-no-show', 'export-students', 'export-receipts', 'term-rollover'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'BURSAR';
                        }
                    ],
                    //  RULE 4: Legacy Teacher
                    [
                        'actions' => ['teacher-grading', 'submit-marks',],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'TEACHER';
                        }
                    ],


                    //  RULE 5: Legacy Director of Studies (DOS) Academic Moderation (Keep intact)
                    [
                        'actions' => ['dos-review', 'seal-marks', 'print-reports', 'manage-assignments', 'delete-assignment'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'DOS';
                        }
                    ],

                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->session->setFlash('error', 'Access Denied: Insufficient authorization privileges for this desk.');
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'process-payment' => ['post'],
                    'submit-marks' => ['post'], 
                    'seal-marks' => ['post'],
                ],
            ],
        ];
    }




    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
        ];
    }

    /**
     * @return string
     */
     public function actionIndex()
    {
        $model = new StudentLookup();
        return $this->render('index', [
            'model' => $model,
        ]);
    }

   
     
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            $role = Yii::$app->user->identity->role;
            if ($role === 'SUPER_ADMIN') {
                return $this->redirect(['site/super-admin']);
            } elseif ($role === 'SCHOOL_ADMIN') {
                return $this->redirect(['site/school-admin']); 
            } elseif ($role === 'DOS') {
                return $this->redirect(['site/dos-review']);
            } elseif ($role === 'TEACHER') {
                return $this->redirect(['site/teacher-grading']);
            } elseif ($role === 'CANTEEN') {
                return $this->redirect(['site/canteen-terminal']);
            }
            return $this->redirect(['site/bursar']);
        }

        $model = new LoginForm($this->security);

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $role = Yii::$app->user->identity->role;
            
            if ($role === 'SUPER_ADMIN') {
                return $this->redirect(['site/super-admin']);
            } elseif ($role === 'SCHOOL_ADMIN') {
                return $this->redirect(['site/school-admin']); 
            } elseif ($role === 'DOS') {
                return $this->redirect(['site/dos-review']);
            } elseif ($role === 'TEACHER') {
                return $this->redirect(['site/teacher-grading']);
            } elseif ($role === 'CANTEEN') {
                return $this->redirect(['site/canteen-terminal']);
            }
            return $this->redirect(['site/bursar']);
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }


    /**
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     *
     * @return Response|string
     */
    public function actionContact(): Response|string
    {
        $model = new ContactForm();

        $contact = $model->load($this->request->post()) && $model->contact(
            $this->mailer,
            Yii::$app->params['adminEmail'],
            Yii::$app->params['senderEmail'],
            Yii::$app->params['senderName'],
        );

        if ($contact) {
            Yii::$app->session->setFlash(
                'success',
                'Thank you for contacting us. We will respond to you as soon as possible.',
            );

            return $this->refresh();
        }

        return $this->render('contact', ['model' => $model]);
    }


    public function actionAbout(): string
    {
        return $this->render('about');
    }
   
    public function actionTermsOfService(): string
    {
        return $this->render('terms_of_service');
    }

    public function actionPrivacyPolicy(): string
    {
        return $this->render('privacy_policy');
    }

    public function actionLookup()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StudentLookup();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
            $student = Students::findOne(['payment_code' => $model->payment_code]);

            if ($student) {
                $startOfDay = date('Y-m-d 00:00:00');
                $endOfDay = date('Y-m-d 23:59:59');
                
                $spentToday = (float) Transactions::find()
                    ->where(['student_id' => $student->id, 'transaction_type' => 'CANTEEN_SPEND'])
                    ->andWhere(['between', 'created_at', $startOfDay, $endOfDay])
                    ->sum('amount');

                $remainingLimit = (float)$student->daily_spend_limit - $spentToday;
                if ($remainingLimit < 0) { $remainingLimit = 0; }

                return [
                    'success' => true,
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'school_name' => $student->school ? $student->school->name : 'N/A',
                    'class_level' => $student->class_level,
                    'tuition_balance' => number_format((float)($student->tuition_balance ?? 0), 0),
                    'swallet_balance' => number_format((float)($student->swallet_balance ?? 0), 0),
                    'daily_spend_limit' => number_format($remainingLimit, 0),
                ];
            }


            return ['success' => false, 'message' => 'No student found with that payment code.'];
        }

        return ['success' => false, 'message' => current($model->getFirstErrors())];
    }



    public function actionBursar()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $userSchoolId = Yii::$app->user->identity->school_id;
        $request = Yii::$app->request;

        $stats = [
            'total_tuition'     => (float) Transactions::find()->joinWith('student')->where(['transaction_type' => 'TUITION', 'students.school_id' => $userSchoolId])->sum('amount'),
            'total_outstanding' => (float) Students::find()->where(['school_id' => $userSchoolId])->sum('tuition_balance'),
            'total_swallet'     => (float) Students::find()->where(['school_id' => $userSchoolId])->sum('swallet_balance'),
        ];

        $txSearchKeyword = trim($request->get('tx_q', ''));

        $txQuery = Transactions::find()->joinWith('student')->where(['students.school_id' => $userSchoolId]);
        
        if (!empty($txSearchKeyword)) {
            $txQuery->andWhere([
                'or',
                ['ilike', 'transactions.external_reference', $txSearchKeyword],
                ['ilike', 'transactions.transaction_type', $txSearchKeyword],
                ['ilike', 'transactions.payment_channel', $txSearchKeyword],
                ['ilike', 'students.name', $txSearchKeyword]
            ]);
        }
        
        $txCountQuery = clone $txQuery;
        $txPages = new \yii\data\Pagination([
            'totalCount' => (int) $txCountQuery->count(),
            'pageSize' => 20,
            'pageParam' => 'p_tx',
        ]);
        
        $recentTransactions = $txQuery->offset($txPages->offset)
            ->limit($txPages->limit)
            ->orderBy(['transactions.created_at' => SORT_DESC])
            ->all();

        return $this->render('bursar', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'txPages' => $txPages,
            'txSearchKeyword' => $txSearchKeyword,
        ]);
    }

    public function actionStudentsDirectory()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $userSchoolId = Yii::$app->user->identity->school_id;
        $request = Yii::$app->request;

        $searchKeyword = trim($request->get('q', ''));
        $balanceFilter = trim($request->get('balance_status', 'ALL'));

        $studentQuery = Students::find()->where(['school_id' => $userSchoolId]);
        
        if (!empty($searchKeyword)) {
            $studentQuery->andWhere(['or', ['ilike', 'name', $searchKeyword], ['payment_code' => $searchKeyword]]);
        }
        
        if ($balanceFilter === 'OWING') {
            $studentQuery->andWhere(['>', 'tuition_balance', 0]);
        } elseif ($balanceFilter === 'CLEARED') {
            $studentQuery->andWhere(['<=', 'tuition_balance', 0]);
        }

        $studentCountQuery = clone $studentQuery;
        $studentPages = new \yii\data\Pagination([
            'totalCount' => (int) $studentCountQuery->count(),
            'pageSize' => 20,
            'pageParam' => 'p_student',
        ]);
        
        $allStudents = $studentQuery->offset($studentPages->offset)
            ->limit($studentPages->limit)
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $this->render('students_directory', [
            'allStudents' => $allStudents,
            'studentPages' => $studentPages,
            'searchKeyword' => $searchKeyword,
            'balanceFilter' => $balanceFilter,
        ]);
    }


public function actionProcessPayment()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;

    if ($request->isPost) {
        $paymentCode = $request->post('payment_code');
        $amount = (float) $request->post('amount');
        $type = $request->post('type');

        // validation checks
        if (empty($paymentCode) || $amount <= 0 || !in_array($type, ['TUITION', 'POCKET_MONEY'])) {
            return ['success' => false, 'message' => 'Invalid transaction processing parameters.'];
        }

        // the Target Student
        $student = Students::findOne(['payment_code' => $paymentCode]);
        if (!$student) {
            return ['success' => false, 'message' => 'Target student file not found.'];
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            if ($type === 'TUITION') {
                // Ensure parents aren't accidentally overpaying tuition
                if ($amount > (float)$student->tuition_balance) {
                    return ['success' => false, 'message' => 'Payment exceeds outstanding tuition fees.'];
                }
                $student->tuition_balance -= $amount;
            } else {
                $student->swallet_balance += $amount;
            }

            if (!$student->save()) {
                throw new \Exception('Failed to update student financial balances.');
            }

            $ledger = new Transactions();
            $ledger->student_id = $student->id;
            $ledger->amount = $amount;
            $ledger->transaction_type = $type;
            $ledger->payment_channel = 'ONLINE_PORTAL';
            $ledger->external_reference = 'REF_' . strtoupper(uniqid()); 
            $ledger->status = 'SUCCESS';

            if (!$ledger->save()) {
                throw new \Exception('Failed to commit tracking ledger records.');
            }

            $dbTransaction->commit();

            return [
                'success' => true,
                'message' => 'Transaction settled successfully!',
                'new_tuition' => number_format((float)$student->tuition_balance, 0),
                'new_swallet' => number_format((float)$student->swallet_balance, 0),
            ];

        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            return ['success' => false, 'message' => 'Clearing system exception error: ' . $e->getMessage()];
        }
    }

    return ['success' => false, 'message' => 'Bad Request.'];
}


  
    public function actionSignup()
    {
        if (Yii::$app->user->isGuest) {
            return $this->render('signup_guest_support');
        }

        $currentUser = Yii::$app->user->identity;
        
        // Security Lockout: Only Super Admins and School Admins can provision credentials
        if (!in_array($currentUser->role, ['SUPER_ADMIN', 'SCHOOL_ADMIN', 'DOS'])) {
            throw new \yii\web\ForbiddenHttpException("Unauthorized administrative onboarding access privileges.");
        }

        $model = new SignupForm();

        if (in_array($currentUser->role, ['SCHOOL_ADMIN', 'DOS'])) {
            $model->school_id = $currentUser->school_id; 
        }

        if ($model->load(Yii::$app->request->post())) {
            if (in_array($currentUser->role, ['SCHOOL_ADMIN', 'DOS'])) {
                $model->school_id = $currentUser->school_id;
            }

            if ($model->signup()) {
                $schoolName = 'Our Institution';
                $assignedSchool = Schools::findOne($model->school_id);
                if ($assignedSchool) {
                    $schoolName = $assignedSchool->name;
                }

                $loginUrl = \yii\helpers\Url::toRoute(['site/login'], true);
                
                Yii::$app->mailer->compose()
                    ->setFrom(['marktravis689@gmail.com' => 'EduVest Core ERP Platform'])
                    ->setTo($model->email) 
                    ->setSubject(" Account Credentials Activation — {$schoolName}")
                    ->setHtmlBody("
                        <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>
                            <h2 style='color: #007bff;'>Welcome to the Team, @{$model->username}!</h2>
                            <p>An official staff user account has been successfully provisioned for you at <strong>{$schoolName}</strong>.</p>
                            
                            <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;'>
                                <p style='margin: 5px 0;'><strong>System Role Desk:</strong> {$model->role}</p>
                                <p style='margin: 5px 0;'><strong>Authorized Username:</strong> {$model->username}</p>
                                <p style='margin: 5px 0;'><strong>Temporary Setup Password:</strong> {$model->password}</p>
                            </div>

                            <p><a href='{$loginUrl}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;'>Access Staff Gateway Panel</a></p>
                            <p style='color: #dc3545; font-size: 13px;'> <strong>Notice:</strong> Please make sure to change your temporary setup password immediately upon your first login session.</p>
                        </div>
                    ")
                    ->send();

                Yii::$app->session->setFlash('success', "Staff account '{$model->username}' provisioned! Activation email dispatched to: " . \yii\helpers\Html::encode($model->email));

                if ($currentUser->role === 'SUPER_ADMIN') {
                    return $this->redirect(['site/super-admin']);
                }
                return $this->redirect(['site/school-admin']);
            }

        }

        return $this->render('signup', [
            'model' => $model,
            'currentUserRole' => $currentUser->role
        ]);
    }

       
    public function actionCanteenTerminal()
    {
        $model = new StudentLookup();
        return $this->render('canteen_terminal', [
            'model' => $model,
        ]);
    }

    public function actionCanteenDebit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        if ($request->isPost) {
            $paymentCode = $request->post('payment_code');
            $chargeAmount = (float) $request->post('amount');

            if (empty($paymentCode) || $chargeAmount <= 0) {
                return ['success' => false, 'message' => 'Invalid sale checkout metrics.'];
            }

            $student = Students::findOne(['payment_code' => $paymentCode]);
            if (!$student) {
                return ['success' => false, 'message' => 'Student record not registered on network.'];
            }

            if ($chargeAmount > (float)$student->swallet_balance) {
                return ['success' => false, 'message' => 'Insufficient wallet balance for this purchase.'];
            }

            // Compute Spent Balance Today
            $startOfDay = date('Y-m-d 00:00:00');
            $endOfDay = date('Y-m-d 23:59:59');
            
            $spentToday = (float) Transactions::find()
                ->where(['student_id' => $student->id, 'transaction_type' => 'CANTEEN_SPEND'])
                ->andWhere(['between', 'created_at', $startOfDay, $endOfDay])
                ->sum('amount');

            $remainingLimit = (float)$student->daily_spend_limit - $spentToday;

            if ($chargeAmount > $remainingLimit) {
                return ['success' => false, 'message' => 'Transaction blocked! Purchase exceeds the student\'s remaining daily spending limit of UGX ' . number_format($remainingLimit, 0)];
            }

            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $student->swallet_balance -= $chargeAmount;
                if (!$student->save()) {
                    throw new \Exception('Failed to debit pocket money profile.');
                }

                $ledger = new Transactions();
                $ledger->student_id = $student->id;
                $ledger->amount = $chargeAmount;
                $ledger->transaction_type = 'CANTEEN_SPEND';
                $ledger->payment_channel = 'CANTEEN_POS';
                $ledger->external_reference = 'POS_' . strtoupper(uniqid());
                $ledger->status = 'SUCCESS';

                if (!$ledger->save()) {
                    throw new \Exception('Failed to commit merchant ledger tracking token.');
                }

                $dbTransaction->commit();

                return [
                    'success' => true,
                    'message' => 'Purchase approved successfully!',
                    'new_swallet' => number_format((float)$student->swallet_balance, 0),
                ];

            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                return ['success' => false, 'message' => 'POS core error: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'Bad Request.'];
    }



    public function actionStudentDashboard($code = null)
    {
        if (empty($code)) {
            Yii::$app->session->setFlash('error', 'Please enter a valid student payment code to view the statement.');
            return $this->redirect(['site/index']);
        }

        $student = Students::findOne(['payment_code' => $code]);
        if (!$student) {
            Yii::$app->session->setFlash('error', 'The student payment code entered was not found.');
            return $this->redirect(['site/index']);
        }

        $statementLogs = Transactions::find()
            ->where(['student_id' => $student->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(50)
            ->all();

        return $this->render('student_dashboard', [
            'student' => $student,
            'statementLogs' => $statementLogs
        ]);
    }

    
    public function actionDownloadStatement($code)
    {
        $student = Students::findOne(['payment_code' => $code]);
        if (!$student) {
            throw new \yii\web\NotFoundHttpException("Statement file generation target matching code failed.");
        }

        $data = $this->renderPartial('_statement_pdf', [
            'student' => $student
        ]);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20, 
            'margin_bottom' => 20, 
        ]);

        $mpdf->SetHeader('SchoolPay Transaction Statement||Generated: ' . date('Y-m-d H:i'));
        $mpdf->SetFooter('Statement Code: ' . $code . '||Page {PAGENO} of {nbpg}');

        $mpdf->SetWatermarkText('SCHOOL PAY CLONE', 0.1);
        $mpdf->showWatermarkText = true;

        $mpdf->WriteHTML($data);
        
        $filename = 'Statement_' . $code . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }


    public function actionSuperAdmin()
    {
        $schools = Schools::find()->all();
        return $this->render('super_admin', ['schools' => $schools]);
    }

   
    public function actionCreateSchool()
    {
        // Strict Guard: Force Super Admin authentication clearances
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'SUPER_ADMIN') {
            Yii::$app->session->setFlash('error', 'Unauthorized administrative access level clearance.');
            return $this->redirect(['site/login']);
        }

        $model = new Schools();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            
            //  Open an atomic database transaction to prevent partial data execution crashes
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                if (!$model->save()) {
                    throw new \Exception("Database mapping failure on core schools data table.");
                }

                $schoolAdmin = new User();
                $schoolAdmin->username = trim($model->admin_username);
                $schoolAdmin->password_hash = Yii::$app->security->generatePasswordHash($model->admin_password);
                $schoolAdmin->auth_key = Yii::$app->security->generateRandomString();
                $schoolAdmin->role = 'SCHOOL_ADMIN'; 
                $schoolAdmin->school_id = $model->id;

                if (!$schoolAdmin->save()) {
                    $errors = implode(', ', \yii\helpers\ArrayHelper::getColumn($schoolAdmin->getErrors(), 0));
                    throw new \Exception("Failed to provision School Administrator account profile: " . $errors);
                }

                $loginUrl = \yii\helpers\Url::toRoute(['site/login'], true);
                
                Yii::$app->mailer->compose()
                    ->setFrom(['marktravis689@gmail.com' => 'EduVest SaaS Core ERP Platform'])
                    ->setTo($model->contact_email)
                    ->setSubject(" Onboarding Activation Packet for " . $model->name)
                    ->setHtmlBody("
                        <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; color: #333;'>
                            <h1 style='color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;'>Welcome to EduVest ERP, {$model->name}!</h1>
                            <p>Your institutional multi-tenant space has been successfully initialized on our cloud network server nodes.</p>
                            
                            <div style='background-color: #f8f9fa; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                                <h3 style='margin-top: 0; color: #856404;'><i class='bi bi-shield-lock'></i> Master School Admin Security Credentials</h3>
                                <p style='margin-bottom: 5px;'><strong>Authorized Username:</strong> <span style='font-family: monospace; background: #fff; padding: 2px 6px; border: 1px solid #ddd;'>{$model->admin_username}</span></p>
                                <p style='margin-bottom: 5px;'><strong>Temporary Setup Password:</strong> <span style='font-family: monospace; background: #fff; padding: 2px 6px; border: 1px solid #ddd;'>{$model->admin_password}</span></p>
                                <p style='margin-bottom: 0;'><strong>Platform Role Scope:</strong> <span style='color: #28a745; font-weight: bold;'>SCHOOL_ADMIN (Full Privilege Ownership)</span></p>
                            </div>

                            <p><strong>CRITICAL SECURITY ACTION REQUIRED:</strong></p>
                            <ol>
                                <li>Click the secure gateway connection link below to load your workspace desk:</li>
                                <li><a href='{$loginUrl}' style='display: inline-block; background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 4px; margin: 10px 0;'>Access Secure Staff Portal</a></li>
                                <li style='color: #dc3545; font-weight: bold;'>You MUST change this temporary placeholder password immediately upon your very first login session to prevent tracking exploits!</li>
                            </ol>

                            <p style='margin-top: 30px; font-size: 12px; color: #6c757d; border-top: 1px solid #ddd; padding-top: 10px;'>
                                This is an automated network notification broadcast transmission dispatch. Please do not reply directly to this mail string.<br>
                                © 2026 EduVest Management Systems Core Framework. All rights reserved.
                            </p>
                        </div>
                    ")
                    ->send();

                $dbTransaction->commit();
                
                Yii::$app->session->setFlash('success', "Institution successfully onboarded! Account '{$model->admin_username}' provisioned and secure credentials packet emailed to: " . Html::encode($model->contact_email));
                return $this->redirect(['site/super-admin']);

            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                Yii::$app->session->setFlash('error', "Multi-tenant deployment aborted: " . $e->getMessage());
            }
        }

        return $this->render('create_school', ['model' => $model]);
    }

         public function actionRegisterStudent()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN'])) {
            Yii::$app->session->setFlash('error', 'Unauthorized administrative access level clearance.');
            return $this->redirect(['site/login']);
        }

        $userSchoolId = Yii::$app->user->identity->school_id;
        $school = Schools::findOne($userSchoolId);

        if (!$school) {
            Yii::$app->session->setFlash('error', 'Your administrative account is not assigned to a valid school structure.');
            return $this->redirect(['site/bursar']);
        }

        $studentModel = new Students();

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post('Students');
            
            $studentModel->school_id = $userSchoolId;
            $studentModel->name = trim($postData['name'] ?? '');
            $studentModel->class_level = trim($postData['class_level'] ?? '');
            
            $studentModel->optional_subjects = trim($postData['optional_subjects'] ?? '');
            $studentModel->a_level_combination = strtoupper(trim($postData['a_level_combination'] ?? ''));
            
            // Apply School Rule
            $studentModel->tuition_balance = (float)$school->base_tuition_fees;
            $studentModel->swallet_balance = 0.00;
            $studentModel->daily_spend_limit = 5000.00; 
            $studentModel->status = 'ACTIVE';

            // Formula: 10 + 2-digit school ID prefix + 6 random digits
            $schoolPrefix = str_pad((string)$userSchoolId, 2, '0', STR_PAD_LEFT);
            $randomSequence = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $generatedCode = '10' . $schoolPrefix . $randomSequence;

            // Integrity safeguard check
            while (Students::findOne(['payment_code' => $generatedCode])) {
                $randomSequence = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $generatedCode = '10' . $schoolPrefix . $randomSequence;
            }

            $studentModel->payment_code = $generatedCode;

            if ($studentModel->save(false)) {
                Yii::$app->session->setFlash('success', "Student file compiled successfully! Generated SchoolPay Code: " . $generatedCode);
                return $this->redirect(['site/bursar']);
            } else {
                Yii::$app->session->setFlash('error', 'Database mapping crash. Enrollment aborted.');
            }
        }

        return $this->render('register_student', [
            'model' => $studentModel,
            'schoolName' => $school->name,
            'baseFees' => $school->base_tuition_fees
        ]);
    }
        
    public function actionEditSchool($id)
    {
        $model = Schools::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Target school record not found.");
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('Schools');
            $model->name = $post['name'];
            $model->bank_account = $post['bank_account'];
            $model->base_tuition_fees = (float)$post['base_tuition_fees'];
            $model->contact_email = $post['contact_email'];

            if ($model->save()) {
                Yii::$app->session->setFlash('success', "School structural parameters updated cleanly.");
                return $this->redirect(['site/super-admin']);
            }
        }

        return $this->render('edit_school', ['model' => $model]);
    }

    
    public function actionEditStudent($id)
    {
        $userSchoolId = Yii::$app->user->identity->school_id;
        
        $model = Students::findOne(['id' => $id, 'school_id' => $userSchoolId]);
        if (!$model) {
            throw new \yii\web\ForbiddenHttpException("Unauthorized file manipulation request.");
        }

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post('Students');
            $model->name = trim($postData['name'] ?? '');
            $model->class_level = trim($postData['class_level'] ?? '');
            $model->daily_spend_limit = (float)($postData['daily_spend_limit'] ?? 5000);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Student registration details modified successfully.");
                return $this->redirect(['site/bursar']);
            }
        }

        return $this->render('edit_student', ['model' => $model]);
    }
 
    public function actionExportStudents()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'BURSAR') {
            throw new \yii\web\ForbiddenHttpException();
        }

        $userSchoolId = Yii::$app->user->identity->school_id;
        $request = Yii::$app->request;
        
        $q = trim($request->get('q', ''));
        $status = trim($request->get('balance_status', 'ALL'));

        $query = Students::find()->where(['school_id' => $userSchoolId]);
        if (!empty($q)) {
            $query->andWhere(['or', ['ilike', 'name', $q], ['payment_code' => $q]]);
        }
        if ($status === 'OWING') {
            $query->andWhere(['>', 'tuition_balance', 0]);
        } elseif ($status === 'CLEARED') {
            $query->andWhere(['=', 'tuition_balance', 0]);
        }

        $students = $query->orderBy(['name' => SORT_ASC])->all();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Student_Report_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Student Full Name', 'Class Level', '10-Digit SchoolPay Code', 'Tuition Balance (UGX)', 'S-Wallet Balance (UGX)']);

        foreach ($students as $st) {
            fputcsv($output, [$st->name, $st->class_level, $st->payment_code, $st->tuition_balance, $st->swallet_balance]);
        }
        fclose($output);
        exit;
    }

    
    public function actionExportReceipts()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'BURSAR') {
            throw new \yii\web\ForbiddenHttpException();
        }

        $userSchoolId = Yii::$app->user->identity->school_id;
        $tx_q = trim(Yii::$app->request->get('tx_q', ''));

        $query = Transactions::find()->joinWith('student')->where(['students.school_id' => $userSchoolId]);
        if (!empty($tx_q)) {
            $query->andWhere(['or', ['ilike', 'transactions.external_reference', $tx_q], ['ilike', 'transactions.transaction_type', $tx_q], ['ilike', 'transactions.payment_channel', $tx_q], ['ilike', 'students.name', $tx_q]]);
        }

        $transactions = $query->orderBy(['transactions.created_at' => SORT_DESC])->all();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Clearing_Receipts_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Settlement Timestamp', 'Student Name', 'Allocation Profile', 'Gateway Channel', 'Clearing Token Reference', 'Amount (UGX)']);

        foreach ($transactions as $tx) {
            fputcsv($output, [$tx->created_at, $tx->student ? $tx->student->name : 'N/A', $tx->transaction_type, $tx->payment_channel, $tx->external_reference, $tx->amount]);
        }
        fclose($output);
        exit;
    }

      
    public function actionDeleteStudent($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== ['BURSAR','SCHOOL_ADMIN']) {
            throw new \yii\web\ForbiddenHttpException("Unauthorized administrative credentials.");
        }

        $userSchoolId = Yii::$app->user->identity->school_id;

        $student = Students::findOne(['id' => $id, 'school_id' => $userSchoolId]);
        if (!$student) {
            throw new \yii\web\NotFoundHttpException("The requested student record was not found.");
        }

        // balance reconciliation verification check
        if ((float)$student->tuition_balance > 0) {
            Yii::$app->session->setFlash('error', "Deletion Blocked! Student cannot be removed while they still owe an outstanding tuition fees balance of UGX " . number_format((float)$student->tuition_balance, 0));
            return $this->redirect(['site/bursar']);
        }

        if ((float)$student->swallet_balance > 0) {
            Yii::$app->session->setFlash('error', "Deletion Blocked! Student profile contains unspent funds. Please clear or refund their S-Wallet vault cash balance of UGX " . number_format((float)$student->swallet_balance, 0) . " before deleting.");
            return $this->redirect(['site/bursar']);
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            //If you have matching transaction ledger line rows, cascade delete them or leave them unlinked
            Transactions::deleteAll(['student_id' => $student->id]);
            
            if ($student->delete()) {
                $dbTransaction->commit();
                Yii::$app->session->setFlash('success', "Student profile '{$student->name}' has been successfully removed from the institution's registry.");
            } else {
                throw new \Exception("Database failed to clear ActiveRecord row context.");
            }
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            Yii::$app->session->setFlash('error', "Wipe operation execution aborted: " . $e->getMessage());
        }

        return $this->redirect(['site/bursar']);
    }

    
    public function actionMarkNoShow($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== ['BURSAR','SCHOOL_ADMIN']) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $userSchoolId = Yii::$app->user->identity->school_id;

        $student = Students::findOne(['id' => $id, 'school_id' => $userSchoolId]);
        if (!$student) {
            throw new \yii\web\NotFoundHttpException("Student record tracking target failed.");
        }

        if ((float)$student->swallet_balance > 0) {
            Yii::$app->session->setFlash('error', "Cannot mark as No-Show! Student has an active S-Wallet cash vault balance of UGX " . number_format((float)$student->swallet_balance, 0) . ". Please refund the wallet funds first.");
            return $this->redirect(['site/bursar']);
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $student->status = 'NO_SHOW';
            
            $student->tuition_balance = 0.00;

            if ($student->save(false)) { 
                $dbTransaction->commit();
                Yii::$app->session->setFlash('success', "Student '{$student->name}' has been marked as a No-Show. Outstanding billing ledger balances zeroed out.");
            } else {
                throw new \Exception("Database failed to update record state fields.");
            }
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            Yii::$app->session->setFlash('error', "Operation execution failed: " . $e->getMessage());
        }

        return $this->redirect(['site/bursar']);
    }

     
    public function actionTermRollover()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== ['BURSAR','SCHOOL_ADMIN']) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $userSchoolId = Yii::$app->user->identity->school_id;
        $school = Schools::findOne($userSchoolId);

        if (!$school) {
            throw new \yii\web\NotFoundHttpException("School billing configuration missing.");
        }

        //only active, attending student records
        $students = Students::find()
            ->where(['school_id' => $userSchoolId, 'status' => 'ACTIVE'])
            ->all();

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($students as $st) {
                //  Tuition accumulates debt while S-Wallet stays untouched
                $st->tuition_balance = (float)$st->tuition_balance + (float)$school->base_tuition_fees;
                
                
                if (!$st->save(false)) {
                    throw new \Exception("Rollover script failed on student code: " . $st->payment_code);
                }
            }

            $dbTransaction->commit();
            Yii::$app->session->setFlash('success', "Academic term rollover processed! Billed " . count($students) . " students. Pocket money vaults maintained smoothly.");
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            Yii::$app->session->setFlash('error', "Rollover aborted: " . $e->getMessage());
        }

        return $this->redirect(['site/bursar']);
    }


    
    public function actionTeacherGrading()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['TEACHER', 'SCHOOL_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $teacherId = Yii::$app->user->identity->id;
        $schoolId = Yii::$app->user->identity->school_id;
        $request = Yii::$app->request;

        // 1. Fetch only the class levels and subjects assigned strictly to this teacher
        $assignments = Yii::$app->db->createCommand(
            'SELECT id, class_level, subject_name FROM teacher_assignments WHERE teacher_id = :tid AND school_id = :sid'
        )->bindValues([':tid' => $teacherId, ':sid' => $schoolId])->queryAll();

        // 2. Read selected filters from the query line
        $selectedAssignmentId = (int)$request->get('assignment_id', 0);
        $selectedTerm = $request->get('term', 'TERM_1');
        $academicYear = (int)date('Y');

        $activeAssignment = null;
        $studentsList = [];
        $existingMarks = [];

        if (!empty($assignments)) {
            // Default to the first assignment if none is chosen yet
            $activeAssignment = $assignments[0];
            foreach ($assignments as $asg) {
                if ((int)$asg['id'] === $selectedAssignmentId) {
                    $activeAssignment = $asg;
                    break;
                }
            }

            $classLevel = $activeAssignment['class_level'];
            $subjectName = $activeAssignment['subject_name'];

            $studentQuery = Students::find()
                ->where(['school_id' => $schoolId, 'class_level' => $classLevel, 'status' => 'ACTIVE']);

            if (strpos($classLevel, 'Primary') !== false) {
                // Primary Tiers: Everyone takes all 4 fixed core subjects automatically
            } 
            elseif (strpos($classLevel, 'Senior 1') !== false || strpos($classLevel, 'Senior 2') !== false || strpos($classLevel, 'Senior 3') !== false || strpos($classLevel, 'Senior 4') !== false) {
                
                $compulsoryOLevel = ['Mathematics', 'English', 'Biology', 'Chemistry', 'Physics', 'History', 'Geography', 'Entrepreneurship'];
                
                if (!in_array($subjectName, $compulsoryOLevel)) {
                    // Optionals: Filter strictly for students who have this elective assigned
                    $studentQuery->andWhere(['ilike', 'optional_subjects', $subjectName]);
                }
            } 
            elseif (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false) {
                // A-Level Combinations: Filter strictly for core combination letters 
                if (!in_array($subjectName, ['General Paper', 'Sub-Math', 'Sub-ICT'])) {
                    $subjectLetter = '';
                    if ($subjectName === 'Physics') $subjectLetter = 'P';
                    elseif ($subjectName === 'Chemistry') $subjectLetter = 'C';
                    elseif ($subjectName === 'Mathematics') $subjectLetter = 'M';
                    elseif ($subjectName === 'Biology') $subjectLetter = 'B';
                    elseif ($subjectName === 'History') $subjectLetter = 'H';
                    elseif ($subjectName === 'Economics') $subjectLetter = 'E';
                    elseif ($subjectName === 'Geography') $subjectLetter = 'G';
                    elseif ($subjectName === 'Literature') $subjectLetter = 'L';

                    if (!empty($subjectLetter)) {
                        $studentQuery->andWhere(['ilike', 'a_level_combination', $subjectLetter]);
                    }
                }
            }

            $studentsList = $studentQuery->orderBy(['name' => SORT_ASC])->all();

            //  Map already entered marks to prevent teachers from losing draft entries
            $marksRows = Yii::$app->db->createCommand(
                'SELECT student_id, bot_mark, mot_mark, eot_mark, teacher_comment, status FROM academic_marks 
                 WHERE school_id = :sid AND class_level = :cls AND subject_name = :sub AND term = :trm AND academic_year = :yr'
            )->bindValues([
                ':sid' => $schoolId,
                ':cls' => $activeAssignment['class_level'],
                ':sub' => $activeAssignment['subject_name'],
                ':trm' => $selectedTerm,
                ':yr'  => $academicYear
            ])->queryAll();

            foreach ($marksRows as $row) {
                $existingMarks[$row['student_id']] = $row;
            }
        }

        return $this->render('teacher_grading', [
            'assignments' => $assignments,
            'activeAssignment' => $activeAssignment,
            'selectedTerm' => $selectedTerm,
            'studentsList' => $studentsList,
            'existingMarks' => $existingMarks,
        ]);
    }


       public function actionSubmitMarks()
    {
        if (Yii::$app->request->isPost && !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'TEACHER') {
            $schoolId = Yii::$app->user->identity->school_id;
            $postData = Yii::$app->request->post();
            
            $classLevel = $postData['class_level'] ?? '';
            $subjectName = $postData['subject_name'] ?? '';
            $term = $postData['term'] ?? 'TERM_1';
            $academicYear = (int)date('Y');

            $scoresMatrix = $postData['Scores'] ?? [];

            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($scoresMatrix as $studentId => $marks) {
                    
                    // Core validation parameter boundary protections
                    $bot = min(100, max(0, (float)($marks['bot'] ?? 0)));
                    $mot = min(100, max(0, (float)($marks['mot'] ?? 0)));
                    $eot = min(100, max(0, (float)($marks['eot'] ?? 0)));
                    $comment = trim($marks['comment'] ?? '');

                    // Check if a row already exists to decide between INSERT or UPDATE operations
                    $exists = Yii::$app->db->createCommand(
                        'SELECT id, status FROM academic_marks WHERE student_id = :st AND subject_name = :sub AND term = :trm AND academic_year = :yr'
                    )->bindValues([':st' => $studentId, ':sub' => $subjectName, ':trm' => $term, ':yr' => $academicYear])->queryOne();

                                         if ($exists) {
                        if ($exists['status'] === 'APPROVED_SEALED') {
                            continue; 
                        }

                        // Clear the D.O.S note when the teacher resubmits the file
                        Yii::$app->db->createCommand()->update('academic_marks', [
                            'bot_mark' => $bot, 
                            'mot_mark' => $mot, 
                            'eot_mark' => $eot,
                            'teacher_comment' => $comment, 
                            'dos_feedback' => null, 
                            'status' => 'PENDING_REVIEW',
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'id = ' . $exists['id'])->execute();
                    }

                }

                $dbTransaction->commit();
                Yii::$app->session->setFlash('success', "Marks entry directory sheet committed to moderation review queue successfully.");
            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                Yii::$app->session->setFlash('error', "Grading commit crash: " . $e->getMessage());
            }

            return $this->redirect(['site/teacher-grading', 'assignment_id' => $postData['assignment_id'] ?? 0, 'term' => $term]);
        }
        throw new \yii\web\ForbiddenHttpException();
    }

       
    public function actionDosReview()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN','SCHOOL_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $schoolId = Yii::$app->user->identity->school_id;
        $request = Yii::$app->request;
        
        $selectedClass = $request->get('class_level', 'Senior 1');
        $selectedTerm = $request->get('term', 'TERM_1');
        $academicYear = (int)date('Y');

        $marksSummary = Yii::$app->db->createCommand(
            'SELECT subject_name, status, COUNT(id) as total_entries 
             FROM academic_marks 
             WHERE school_id = :sid AND class_level = :cls AND term = :trm AND academic_year = :yr
             GROUP BY subject_name, status'
        )->bindValues([
            ':sid' => $schoolId, ':cls' => $selectedClass, ':trm' => $selectedTerm, ':yr' => $academicYear
        ])->queryAll();

        $rawRecords = Yii::$app->db->createCommand(
            'SELECT m.*, s.name as student_name 
             FROM academic_marks m
             JOIN students s ON m.student_id = s.id
             WHERE m.school_id = :sid AND m.class_level = :cls AND m.term = :trm AND m.academic_year = :yr
             ORDER BY m.subject_name ASC, s.name ASC'
        )->bindValues([
            ':sid' => $schoolId, ':cls' => $selectedClass, ':trm' => $selectedTerm, ':yr' => $academicYear
        ])->queryAll();

        return $this->render('dos_review', [
            'marksSummary' => $marksSummary,
            'rawRecords' => $rawRecords,
            'selectedClass' => $selectedClass,
            'selectedTerm' => $selectedTerm,
        ]);
    }

   
    public function actionSealMarks()
    {
        if (Yii::$app->request->isPost && !Yii::$app->user->isGuest && in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN','SCHOOL_ADMIN'])) {
            $schoolId = Yii::$app->user->identity->school_id;
            $classLevel = Yii::$app->request->post('class_level');
            $subjectName = Yii::$app->request->post('subject_name');
            $term = Yii::$app->request->post('term');
            $academicYear = (int)date('Y');

            Yii::$app->db->createCommand()->update('academic_marks', [
                'status' => 'APPROVED_SEALED'
            ], [
                'school_id' => $schoolId,
                'class_level' => $classLevel,
                'subject_name' => $subjectName,
                'term' => $term,
                'academic_year' => $academicYear
            ])->execute();

            Yii::$app->session->setFlash('success', "Marks sheet for {$classLevel} — {$subjectName} successfully approved and sealed.");
            return $this->redirect(['site/dos-review', 'class_level' => $classLevel, 'term' => $term]);
        }
        throw new \yii\web\ForbiddenHttpException();
    }


   
    public function actionManageAssignments()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN','SCHOOL_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $schoolId = Yii::$app->user->identity->school_id;

        // Fetch all teachers registered under this specific school
        $teachers = User::find()
            ->where(['school_id' => $schoolId, 'role' => 'TEACHER'])
            ->orderBy(['username' => SORT_ASC])
            ->all();

        // Fetch all existing assignments across the institution
        $activeAssignments = Yii::$app->db->createCommand(
            'SELECT a.*, u.username as teacher_name 
             FROM teacher_assignments a
             JOIN system_admins u ON a.teacher_id = u.id
             WHERE a.school_id = :sid
             ORDER BY u.username ASC, a.class_level ASC'
        )->bindValue(':sid', $schoolId)->queryAll();

        //  Handle new assignment submissions
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            
            Yii::$app->db->createCommand()->insert('teacher_assignments', [
                'school_id' => $schoolId,
                'teacher_id' => (int)($postData['teacher_id'] ?? 0),
                'class_level' => trim($postData['class_level'] ?? ''),
                'subject_name' => trim($postData['subject_name'] ?? ''),
            ])->execute();

            Yii::$app->session->setFlash('success', "New subject assignment mapped successfully.");
            return $this->redirect(['site/manage-assignments']);
        }

        return $this->render('manage_assignments', [
            'teachers' => $teachers,
            'activeAssignments' => $activeAssignments,
        ]);
    }

    
    public function actionDeleteAssignment($id)
    {
        if (Yii::$app->request->isPost && !Yii::$app->user->isGuest && in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN','SCHOOL_ADMIN'])) {
            $schoolId = Yii::$app->user->identity->school_id;
            
            Yii::$app->db->createCommand()
                ->delete('teacher_assignments', 'id = :id AND school_id = :sid', [':id' => $id, ':sid' => $schoolId])
                ->execute();

            Yii::$app->session->setFlash('success', "Teacher subject assignment revoked.");
            return $this->redirect(['site/manage-assignments']);
        }
        throw new \yii\web\ForbiddenHttpException();
    }


     
    
    public function actionPrintReports()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN','SCHOOL_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $schoolId = Yii::$app->user->identity->school_id;
        $request = Yii::$app->request;
        
        $selectedClass = $request->get('class_level', 'Senior 1');

        // Fetch all active students in the selected class layer to present print options
        $students = Students::find()
            ->where(['school_id' => $schoolId, 'class_level' => $selectedClass, 'status' => 'ACTIVE'])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $this->render('print_reports', [
            'students' => $students,
            'selectedClass' => $selectedClass,
        ]);
    }

   
    public function actionViewReportCard($id, $term = 'TERM_1')
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $schoolId = Yii::$app->user->identity->school_id;
        $academicYear = (int)date('Y');

        $student = Students::findOne(['id' => $id, 'school_id' => $schoolId]);
        if (!$student) {
            throw new \yii\web\NotFoundHttpException("Target student record profile file not found.");
        }

        //  Fetch all sealed or pending marks for this child matching selected parameters
        $gradesList = Yii::$app->db->createCommand(
            'SELECT * FROM academic_marks 
             WHERE student_id = :sid AND term = :trm AND academic_year = :yr'
        )->bindValues([':sid' => $id, ':trm' => $term, ':yr' => $academicYear])->queryAll();

        return $this->render('view_report_card', [
            'student' => $student,
            'gradesList' => $gradesList,
            'term' => $term,
            'year' => $academicYear
        ]);
    }

      
    public function actionRejectMarks()
    {
        if (Yii::$app->request->isPost && !Yii::$app->user->isGuest && in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN'])) {
            $schoolId = Yii::$app->user->identity->school_id;
            $request = Yii::$app->request;
            
            $classLevel = $request->post('class_level');
            $subjectName = $request->post('subject_name');
            $term = $request->post('term');
            $dosComment = trim($request->post('dos_comment', ''));
            $academicYear = (int)date('Y');

            if (empty($dosComment)) {
                Yii::$app->session->setFlash('error', 'You must provide a rejection correction comment for the teacher.');
                return $this->redirect(['site/dos-review', 'class_level' => $classLevel, 'term' => $term]);
            }

                        // Save the D.O.S rejection notes strictly inside the new feedback box
            Yii::$app->db->createCommand()->update('academic_marks', [
                'status' => 'REJECTED_AMEND',
                'dos_feedback' => new \yii\db\Expression(":dosComment::text", [':dosComment' => $dosComment])
            ], [
                'school_id' => $schoolId,
                'class_level' => $classLevel,
                'subject_name' => $subjectName,
                'term' => $term,
                'academic_year' => $academicYear,
                'status' => 'PENDING_REVIEW'
            ])->execute();
            Yii::$app->session->setFlash('success', "Marks sheet for {$classLevel} — {$subjectName} has been rejected back to the teacher.");
            return $this->redirect(['site/dos-review', 'class_level' => $classLevel, 'term' => $term]);
        }
        throw new \yii\web\ForbiddenHttpException();
    }


    public function actionSchoolAdmin()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'SCHOOL_ADMIN') {
            return $this->redirect(['site/login']);
        }

        $schoolId = Yii::$app->user->identity->school_id;
        $school = Schools::findOne($schoolId);

        if (!$school) {
            throw new \yii\web\NotFoundHttpException("Institution configuration matrix corrupted.");
        }

        //  INHERITED kpi METRICS: Aggregate real-time analytics across all school departments
        $stats = [
            'total_collected' => (float) Transactions::find()->joinWith('student')->where(['transaction_type' => 'TUITION', 'students.school_id' => $schoolId])->sum('amount'),
            'total_outstanding' => (float) Students::find()->where(['school_id' => $schoolId])->sum('tuition_balance'),
            'active_students' => (int) Students::find()->where(['school_id' => $schoolId, 'status' => 'ACTIVE'])->count(),
            'total_staff' => (int) User::find()->where(['school_id' => $schoolId])->count(),
        ];

        //Monitor pending grading sheets submitted by teachers
        $pendingSheets = Yii::$app->db->createCommand(
            "SELECT subject_name, class_level, term, COUNT(id) as student_count 
             FROM academic_marks 
             WHERE school_id = :sid AND status = 'PENDING_REVIEW'
             GROUP BY subject_name, class_level, term
             LIMIT 5"
        )->bindValue(':sid', $schoolId)->queryAll();

        // Combined stream of latest clearing network receipts
        $recentTransactions = Transactions::find()
            ->joinWith('student')
            ->where(['students.school_id' => $schoolId])
            ->orderBy(['transactions.created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('school_admin', [
            'school' => $school,
            'stats' => $stats,
            'pendingSheets' => $pendingSheets,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}