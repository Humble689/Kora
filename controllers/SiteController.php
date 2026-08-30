<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\helpers\Html;

use app\models\ContactForm;
use app\models\ExpenseClaims;
use app\models\LoginForm;
use app\models\PosDevices;
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
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\Url;
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
                'export-students', 'export-receipts', 'term-rollover', 'students-directory',
                'teacher-grading', 'submit-marks', 'dos-review', 'seal-marks', 'print-reports',
                'logout', 'signup',
                'super-admin', 'create-school', 'edit-school', 'school-admin',
                'manage-assignments', 'delete-assignment', 'register-device',
                'reject-marks', 'bulk-moderate-marks', 'batch-print-reports',
                'export-class-marks', 'wallet-adjust', 'void-transaction', 'batch-invoice',
                'expense-claims', 'expense-claims-create',
                'students-by-class', 'class-list', 'class-student-count',
                'switch-school', 'clear-school-context', 'school-registry',
            ],
            'rules' => [
                //  RULE 1: Super Admin Permissions
                [
                    'actions' => [
                        'super-admin', 'school-registry', 'create-school', 'edit-school', 'school-admin', 'signup', 'bursar',
                        'dos-review', 'seal-marks', 'print-reports', 'manage-assignments', 'students-directory',
                        'delete-assignment', 'register-student', 'edit-student', 'delete-student',
                        'mark-no-show', 'export-students', 'export-receipts', 'term-rollover',
                        'teacher-grading', 'submit-marks',
                        'register-device', 'reject-marks', 'bulk-moderate-marks', 'batch-print-reports',
                        'export-class-marks', 'wallet-adjust', 'void-transaction', 'batch-invoice',
                        'expense-claims', 'expense-claims-create',
                        'students-by-class', 'class-list', 'class-student-count',
                        'switch-school', 'clear-school-context',
                    ],
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'SUPER_ADMIN';
                    }
                ],

                //  RULE 2: Bursar Accounting Permissions
                [
                    'actions' => [
                        'school-admin', 'signup', 'bursar', 'dos-review', 'seal-marks', 'print-reports',
                        'manage-assignments', 'students-directory', 'delete-assignment', 'register-student',
                        'edit-student', 'delete-student', 'mark-no-show', 'export-students', 'export-receipts',
                        'term-rollover',
                        'register-device', 'bulk-moderate-marks', 'batch-print-reports', 'export-class-marks',
                        'wallet-adjust', 'void-transaction', 'batch-invoice',
                        'expense-claims', 'expense-claims-create',
                        'students-by-class', 'class-list', 'class-student-count',
                    ],
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'SCHOOL_ADMIN';
                    }
                ],
                //  RULE 3: Legacy Bursar Accounting 
                [
                    'actions' => [
                        'bursar', 'register-student', 'edit-student', 'delete-student', 'mark-no-show',
                        'export-students', 'export-receipts', 'term-rollover', 'students-directory',
                        'export-class-marks', 'wallet-adjust', 'void-transaction', 'batch-invoice',
                        'expense-claims', 'expense-claims-create',
                        'students-by-class', 'class-list', 'class-student-count',
                    ],
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'BURSAR';
                    }
                ],
                //  RULE 4: Legacy Teacher
                [
                    'actions' => ['teacher-grading', 'submit-marks'],
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'TEACHER';
                    }
                ],
                //  RULE 5: Legacy Director of Studies (DOS) Academic Moderation (Keep intact)
                [
                    'actions' => [
                        'dos-review', 'seal-marks', 'print-reports', 'manage-assignments', 'delete-assignment',
                        'signup', 'reject-marks', 'bulk-moderate-marks', 'batch-print-reports',
                    ],
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


private function tooManyAttempts(string $bucket, int $maxAttempts, int $windowSeconds): bool
{
    $cache = Yii::$app->cache;
    $key = 'ratelimit_' . $bucket . '_' . Yii::$app->request->userIP;
    $now = time();

    $data = $cache->get($key);
    if ($data === false || $data['expires'] < $now) {
        $data = ['count' => 0, 'expires' => $now + $windowSeconds];
    }

    $data['count']++;
    $cache->set($key, $data, $windowSeconds);

    return $data['count'] > $maxAttempts;
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

   
    private function getWorkingSchoolId(): ?int
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            return null;
        }

        if ($user->role === 'SUPER_ADMIN') {
            $id = Yii::$app->session->get('super_admin_school_id');
            return ($id !== null && $id !== '') ? (int) $id : null;
        }

        return $user->school_id !== null ? (int) $user->school_id : null;
    }

    private function getWorkingSchool(): ?Schools
    {
        $id = $this->getWorkingSchoolId();
        return $id ? Schools::findOne($id) : null;
    }

   
    private function requireWorkingSchoolId(): ?int
    {
        $schoolId = $this->getWorkingSchoolId();
        if ($schoolId === null && !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'SUPER_ADMIN') {
            Yii::$app->session->setFlash(
                'error',
                'Select a school first from Master School Registry (Switch into school).'
            );
            Yii::$app->response->redirect(['site/super-admin']);
            Yii::$app->end();
        }
        return $schoolId;
    }

   
    public function actionSwitchSchool($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'SUPER_ADMIN') {
            throw new \yii\web\ForbiddenHttpException('Only Super Admin can switch school context.');
        }

        $school = Schools::findOne((int) $id);
        if (!$school) {
            Yii::$app->session->setFlash('error', 'School not found.');
            return $this->redirect(['site/super-admin']);
        }

        Yii::$app->session->set('super_admin_school_id', (int) $school->id);
        Yii::$app->session->setFlash(
            'success',
            'Now working in: ' . Html::encode($school->name)
        );

        $referrer = Yii::$app->request->referrer;
        if ($referrer && str_contains($referrer, Yii::$app->request->hostInfo)) {
            // Avoid redirect loops back onto switch-school itself
            if (!str_contains($referrer, 'switch-school')) {
                return $this->redirect($referrer);
            }
        }

        return $this->redirect(['site/bursar']);
    }

    public function actionClearSchoolContext()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'SUPER_ADMIN') {
            throw new \yii\web\ForbiddenHttpException();
        }

        Yii::$app->session->remove('super_admin_school_id');
        Yii::$app->session->setFlash('success', 'School context cleared. Platform view only.');
        return $this->redirect(['site/super-admin']);
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

    public function beforeAction($action)
{
    if (!parent::beforeAction($action)) {
        return false;
    }

    if (!Yii::$app->user->isGuest) {
        $identity = Yii::$app->user->identity;
        if ($identity->status !== 'ACTIVE') {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('error', 'Your account has been deactivated. Please contact your administrator.');
            Yii::$app->response->redirect(['site/login'])->send();
            Yii::$app->end();
        }
    }

    return true;
}


public function actionLogin()
{
    if (!Yii::$app->user->isGuest) {
        return $this->redirectUserByRole();
    }

    $model = new LoginForm($this->security);

    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        return $this->redirectUserByRole();
    }

    $user = User::findOne(['username' => $model->username]);

if ($user && $user->status !== 'ACTIVE') {
    Yii::$app->session->setFlash('error', 'This account has been deactivated. Contact your school administrator.');
    return $this->render('login', ['model' => $model]);
}

    $model->password = '';

    return $this->render('login', [
        'model' => $model,
    ]);
}


private function redirectUserByRole()
{
    $role = Yii::$app->user->identity->role;

    return $this->redirect(match ($role) {
        'SUPER_ADMIN'     => ['site/super-admin'],
        'SCHOOL_ADMIN'    => ['site/school-admin'],
        'DOS'             => ['site/dos-review'],
        'TEACHER'         => ['site/teacher-grading'],
        'CANTEEN'         => ['site/canteen-terminal'],
        default           => ['site/bursar'],
    });
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
         if ($this->tooManyAttempts('lookup', 8, 60)) {
        Yii::$app->response->statusCode = 429;
        return ['success' => false, 'message' => 'Too many attempts. Please wait a moment and try again.'];
    }
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
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN','SUPER_ADMIN'])) {
        return $this->redirect(['site/login']);
    }

    $userSchoolId = $this->requireWorkingSchoolId();
    $request = Yii::$app->request;


    $stats = [
        'total_tuition'     => (float) Transactions::find()->joinWith('student')->where(['transaction_type' => 'TUITION', 'transactions.status' => 'SUCCESS', 'students.school_id' => $userSchoolId])->sum('amount'),
        'total_outstanding' => (float) Students::find()->where(['school_id' => $userSchoolId])->sum('tuition_balance'),
        'total_swallet'     => (float) Students::find()->where(['school_id' => $userSchoolId])->sum('swallet_balance'),
    ];
$reconciliationByChannel = Transactions::find()
    ->joinWith('student')
    ->select([
        'transactions.payment_channel',
        'network_cleared' => 'SUM(CASE WHEN transactions.status = \'SUCCESS\' THEN transactions.amount ELSE 0 END)',
        'bank_settled'    => 'SUM(CASE WHEN transactions.bank_settled = true THEN transactions.amount ELSE 0 END)',
    ])
    ->where(['students.school_id' => $userSchoolId, 'transactions.transaction_type' => 'TUITION'])
    ->groupBy(['transactions.payment_channel'])
    ->asArray()
    ->all();

$settlementRelevantChannels = ['MTN_MOMO', 'AIRTEL_MONEY', 'ONLINE_PORTAL'];

$stats['network_cleared'] = 0;
$stats['bank_settled'] = 0;
foreach ($reconciliationByChannel as $row) {
    if (in_array($row['payment_channel'], $settlementRelevantChannels)) {
        $stats['network_cleared'] += (float) $row['network_cleared'];
        $stats['bank_settled'] += (float) $row['bank_settled'];
    }
}
$stats['settlement_gap'] = $stats['network_cleared'] - $stats['bank_settled'];

    $stats['swallet_float'] = $stats['total_swallet'];
    $stats['swallet_7d_topups'] = (float) Transactions::find()
        ->joinWith('student')
        ->where(['students.school_id' => $userSchoolId, 'transaction_type' => 'POCKET_MONEY'])
        ->andWhere(['>=', 'transactions.created_at', date('Y-m-d H:i:s', strtotime('-7 days'))])
        ->sum('amount');

    $stats['canteen_total_collected'] = (float) Transactions::find()
    ->where(['transaction_type' => 'CANTEEN_SPEND', 'status' => 'SUCCESS', 'school_id' => $userSchoolId])
    ->sum('amount');

    $schoolId = $userSchoolId;

$stats['pos_device_count'] = (int) PosDevices::find()
    ->where(['school_id' => $schoolId])
    ->count();

$stats['pos_device_active_count'] = (int) PosDevices::find()
    ->where(['school_id' => $schoolId, 'status' => 'ACTIVE'])
    ->count();


    $stats['canteen_today_collected'] = (float) Transactions::find()
    ->where(['transaction_type' => 'CANTEEN_SPEND', 'status' => 'SUCCESS', 'school_id' => $userSchoolId])
    ->andWhere(['between', 'created_at', date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
    ->sum('amount');

    $channelBreakdown = Transactions::find()
        ->joinWith('student')
        ->select(['transactions.payment_channel', 'total' => 'SUM(transactions.amount)'])
        ->where(['students.school_id' => $userSchoolId])
        ->groupBy(['transactions.payment_channel'])
        ->asArray()
        ->all();

$stats['total_approved_expenses'] = (float) ExpenseClaims::find()
    ->where(['school_id' => $userSchoolId, 'status' => 'APPROVED'])
    ->sum('amount');

$stats['net_available_tuition'] = $stats['total_tuition'] - $stats['total_approved_expenses'];

    $defaulterHeatmap = Students::find()
    ->select(['class_level', 'total_outstanding' => 'SUM(tuition_balance)', 'defaulter_count' => 'COUNT(CASE WHEN tuition_balance > 0 THEN 1 END)'])
    ->where(['school_id' => $userSchoolId])
    ->groupBy(['class_level'])
    ->orderBy(['total_outstanding' => SORT_DESC])
    ->asArray()
    ->all();

    $workingSchool = $this->getWorkingSchool();
    $currentTermId = $workingSchool->current_term_id ?? null;
    $collectionVelocity = $this->buildCollectionVelocitySeries($userSchoolId, $currentTermId);

    $txSearchKeyword = trim($request->get('tx_q', ''));
$txQuery = Transactions::find()
    ->joinWith('student')
    ->where([
        'or',
        ['students.school_id' => $userSchoolId],
        ['transactions.school_id' => $userSchoolId],
    ]);
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
    'channelBreakdown' => $channelBreakdown,
    'reconciliationByChannel' => $reconciliationByChannel,
    'workingSchool' => $this->getWorkingSchool(),
    'settlementRelevantChannels' => $settlementRelevantChannels,
    'defaulterHeatmap' => $defaulterHeatmap,
    'collectionVelocity' => $collectionVelocity,
    'recentTransactions' => $recentTransactions,
    'txPages' => $txPages,
    'txSearchKeyword' => $txSearchKeyword,
]);
}


private function buildCollectionVelocitySeries($schoolId, $currentTermId = null)
{
    $periodDays = 90;

    $currentStart = date('Y-m-d', strtotime("-{$periodDays} days"));
    $currentEnd   = date('Y-m-d 23:59:59');

    $previousStart = date('Y-m-d', strtotime("-" . ($periodDays * 2) . " days"));
    $previousEnd   = date('Y-m-d 23:59:59', strtotime("-{$periodDays} days"));

    $buildSeries = function ($start, $end) use ($schoolId) {
        $rows = Transactions::find()
            ->joinWith('student')
            ->select(['day' => 'DATE(transactions.created_at)', 'daily_total' => 'SUM(transactions.amount)'])
            ->where(['students.school_id' => $schoolId, 'transaction_type' => 'TUITION'])
            ->andWhere(['between', 'transactions.created_at', $start, $end])
            ->groupBy(['day'])
            ->orderBy(['day' => SORT_ASC])
            ->asArray()->all();

        $cumulative = 0;
        return array_map(function ($r) use (&$cumulative) {
            $cumulative += (float) $r['daily_total'];
            return ['day' => $r['day'], 'cumulative' => $cumulative];
        }, $rows);
    };

    return [
        'current' => $buildSeries($currentStart, $currentEnd),
        'previous' => $buildSeries($previousStart, $previousEnd),
    ];

}

 public function actionStudentsDirectory()
{
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN','SUPER_ADMIN'])) {
        return $this->redirect(['site/login']);
    }

    $userSchoolId = $this->requireWorkingSchoolId();
    $request = Yii::$app->request;

    $searchKeyword = trim($request->get('q', ''));
    $balanceFilter = trim($request->get('balance_status', 'ALL'));
    $classLevel    = trim($request->get('class_level', 'ALL'));
    $sort          = trim($request->get('sort', ''));

    $classLevels = Students::find()
        ->select('class_level')
        ->distinct()
        ->where(['school_id' => $userSchoolId])
        ->orderBy(['class_level' => SORT_ASC])
        ->column();

    $studentQuery = Students::find()->where(['school_id' => $userSchoolId]);

    if (!empty($searchKeyword)) {
        $studentQuery->andWhere(['or', ['ilike', 'name', $searchKeyword], ['payment_code' => $searchKeyword]]);
    }

    if ($balanceFilter === 'OWING') {
        $studentQuery->andWhere(['>', 'tuition_balance', 0]);
    } elseif ($balanceFilter === 'CLEARED') {
        $studentQuery->andWhere(['<=', 'tuition_balance', 0]);
    }

    if ($classLevel !== 'ALL' && $classLevel !== '') {
        if (in_array($classLevel, $classLevels, true)) {
            $studentQuery->andWhere(['class_level' => $classLevel]);
        }
    }

    $studentCountQuery = clone $studentQuery;
    $studentPages = new \yii\data\Pagination([
        'totalCount' => (int) $studentCountQuery->count(),
        'pageSize' => 20,
        'pageParam' => 'p_student',
    ]);

    $orderBy = ['name' => SORT_ASC];
    if ($sort === 'class_level') {
        $orderBy = ['class_level' => SORT_ASC, 'name' => SORT_ASC];
    } elseif ($sort === '-class_level') {
        $orderBy = ['class_level' => SORT_DESC, 'name' => SORT_ASC];
    }

    $allStudents = $studentQuery->offset($studentPages->offset)
        ->limit($studentPages->limit)
        ->orderBy($orderBy)
        ->all();

    return $this->render('students_directory', [
        'allStudents' => $allStudents,
        'studentPages' => $studentPages,
        'searchKeyword' => $searchKeyword,
        'balanceFilter' => $balanceFilter,
        'classLevel' => $classLevel,
        'classLevels' => $classLevels,
    ]);
}

public function actionRegisterDevice()
{
    $currentUser = Yii::$app->user->identity;

    if (!in_array($currentUser->role, ['SUPER_ADMIN', 'SCHOOL_ADMIN'], true)) {
        throw new \yii\web\ForbiddenHttpException('You are not authorized to register POS devices.');
    }

    $model = new PosDevices();
    $model->status = 'ACTIVE';

    // Non-super-admins can only register devices for their own school.
    if ($currentUser->role !== 'SUPER_ADMIN') {
        $model->school_id = $currentUser->school_id;
    } else {
        $workingId = $this->getWorkingSchoolId();
        if ($workingId !== null) {
            $model->school_id = $workingId;
        }
    }

    if ($model->load(Yii::$app->request->post())) {
        // Re-lock school_id server-side for non-super-admins
        if ($currentUser->role !== 'SUPER_ADMIN') {
            $model->school_id = $currentUser->school_id;
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', "Device '{$model->label}' registered successfully — UID: {$model->device_uid}");
            return $this->redirect(['site/register-device']);
        }
    }

    $schools = ($currentUser->role === 'SUPER_ADMIN')
        ? \yii\helpers\ArrayHelper::map(Schools::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name')
        : null;

    $devicesQuery = PosDevices::find()->orderBy(['created_at' => SORT_DESC]);
    if ($currentUser->role !== 'SUPER_ADMIN') {
        $devicesQuery->andWhere(['school_id' => $currentUser->school_id]);
    } else {
        $workingId = $this->getWorkingSchoolId();
        if ($workingId !== null) {
            $devicesQuery->andWhere(['school_id' => $workingId]);
        }
    }
    $devices = $devicesQuery->all();

    return $this->render('register-device', [
        'model' => $model,
        'schools' => $schools,
        'devices' => $devices,
        'currentUserRole' => $currentUser->role,
    ]);
}

public function actionProcessPayment()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;

    $idempotencyKey = trim((string) $request->post('idempotency_key'));

if (empty($idempotencyKey)) {
    return ['success' => false, 'message' => 'Missing request identifier. Please refresh and try again.'];
}

$existing = Transactions::findOne(['idempotency_key' => $idempotencyKey]);
if ($existing) {
    return [
        'success' => $existing->status === 'SUCCESS',
        'message' => $existing->status === 'SUCCESS'
            ? 'Thank you!'
            : 'This transaction could not be completed.',
    ];
}

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

    if (!in_array($currentUser->role, ['SUPER_ADMIN', 'SCHOOL_ADMIN', 'DOS'])) {
        throw new \yii\web\ForbiddenHttpException("Unauthorized administrative onboarding access privileges.");
    }

    $model = new SignupForm();
    $model->currentUserRole = $currentUser->role; // <-- new line

    if (in_array($currentUser->role, ['SCHOOL_ADMIN', 'DOS'])) {
        $model->school_id = $currentUser->school_id;
    }

    if ($model->load(Yii::$app->request->post())) {
        $model->currentUserRole = $currentUser->role; // <-- reassert after load() overwrites attributes

        if (in_array($currentUser->role, ['SCHOOL_ADMIN', 'DOS'])) {
            $model->school_id = $currentUser->school_id;
        }

        if ($model->signup()) {
            // ...rest unchanged...
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
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }

    $model = new StudentLookup();
    $currentUser = Yii::$app->user->identity;
    $assignedDevice = PosDevices::findOne(['assigned_staff_id' => $currentUser->id]);

    return $this->render('canteen_terminal', [
        'model' => $model,
        'assignedDevice' => $assignedDevice,
    ]);
}

private function tooManyAttemptsForUser(string $bucket, int $maxAttempts, int $windowSeconds): bool
{
    $cache = Yii::$app->cache;
    $userId = Yii::$app->user->isGuest ? 'guest_' . Yii::$app->request->userIP : Yii::$app->user->id;
    $key = 'ratelimit_' . $bucket . '_' . $userId;
    $now = time();

    $data = $cache->get($key);
    if ($data === false || $data['expires'] < $now) {
        $data = ['count' => 0, 'expires' => $now + $windowSeconds];
    }

    $data['count']++;
    $cache->set($key, $data, $windowSeconds);

    return $data['count'] > $maxAttempts;
}

public function actionCanteenDebit()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;if ($this->tooManyAttemptsForUser('canteen_debit', 20, 60)) {
    return ['success' => false, 'message' => 'Too many requests in a short time. Please slow down.'];
}

    $idempotencyKey = trim((string) $request->post('idempotency_key'));

    if (empty($idempotencyKey)) {
        return ['success' => false, 'message' => 'Missing request identifier. Please refresh and try again.'];
    }

    $existing = Transactions::findOne(['idempotency_key' => $idempotencyKey]);
    if ($existing) {
        return [
            'success' => $existing->status === 'SUCCESS',
            'message' => $existing->status === 'SUCCESS'
                ? 'Thank you!'
                : 'This transaction could not be completed.',
        ];
    }

    if (!$request->isPost) {
        return ['success' => false, 'message' => 'Bad Request.'];
    }

    $paymentCode = $request->post('payment_code');
    $chargeAmount = (float) $request->post('amount');

    if (empty($paymentCode) || $chargeAmount <= 0) {
        return ['success' => false, 'message' => 'Invalid sale checkout metrics.'];
    }

    $currentUser = Yii::$app->user->identity;
    $device = PosDevices::findOne(['assigned_staff_id' => $currentUser->id]);

    if (!$device) {
        return ['success' => false, 'message' => 'No terminal is assigned to your account. Contact your administrator.'];
    }

    if ($device->status !== 'ACTIVE') {
        return ['success' => false, 'message' => 'This terminal has been deactivated. Contact your administrator.'];
    }

    $dbTransaction = Yii::$app->db->beginTransaction();
    try {
        $student = Students::findBySql(
            'SELECT * FROM ' . Students::tableName() . ' WHERE payment_code = :code FOR UPDATE',
            [':code' => $paymentCode]
        )->one();

        if (!$student) {
            $dbTransaction->rollBack();
            return ['success' => false, 'message' => 'Student record not registered on network.'];
        }

        if (empty($student->school_id)) {
            $dbTransaction->rollBack();
            return [
                'success' => false,
                'message' => 'This student has no school assigned. Please update the student profile before processing a canteen purchase.',
            ];
        }

        if ($chargeAmount > (float) $student->swallet_balance) {
            $dbTransaction->rollBack();
            return ['success' => false, 'message' => 'Insufficient wallet balance for this purchase.'];
        }

        $startOfDay = date('Y-m-d 00:00:00');
        $endOfDay = date('Y-m-d 23:59:59');

        $spentToday = (float) Transactions::find()
            ->where(['student_id' => $student->id, 'transaction_type' => 'CANTEEN_SPEND'])
            ->andWhere(['between', 'created_at', $startOfDay, $endOfDay])
            ->sum('amount');

        $remainingLimit = (float) $student->daily_spend_limit - $spentToday;

        if ($chargeAmount > $remainingLimit) {
            $dbTransaction->rollBack();
            return [
                'success' => false,
                'message' => 'Transaction blocked! Purchase exceeds the student\'s remaining daily spending limit of UGX ' . number_format($remainingLimit, 0),
            ];
        }

        $student->swallet_balance -= $chargeAmount;
        if (!$student->save()) {
            throw new \Exception('Failed to debit pocket money profile.');
        }

        $ledger = new Transactions();
        $ledger->student_id = $student->id;
        $ledger->device_id = $device->id;
        $ledger->school_id = $device->school_id;
        $ledger->amount = $chargeAmount;
        $ledger->transaction_type = 'CANTEEN_SPEND';
        $ledger->payment_channel = 'CANTEEN_POS';
        $ledger->external_reference = 'POS_' . strtoupper(uniqid());
        $ledger->status = 'SUCCESS';

        if (!$ledger->save()) {
            throw new \Exception('Failed to commit merchant ledger tracking token.');
        }

        $device->last_synced_at = date('Y-m-d H:i:s');
        $device->save(false);

        $dbTransaction->commit();

        $newRemainingLimit = $remainingLimit - $chargeAmount;

        return [
            'success' => true,
            'message' => 'Purchase approved successfully!',
            'new_swallet' => number_format((float) $student->swallet_balance, 0),
            'new_remaining_limit' => number_format($newRemainingLimit, 0),
        ];

    } catch (\Exception $e) {
        $dbTransaction->rollBack();
        return ['success' => false, 'message' => 'POS core error: ' . $e->getMessage()];
    }
}



public function actionCanteenTransactions()
{
    $searchQuery = trim((string) Yii::$app->request->get('q', ''));

    $currentSchoolId = $this->requireWorkingSchoolId();

    $query = Transactions::find()
        ->alias('t')
        ->joinWith(['student', 'school', 'device'])
        ->where([
            't.transaction_type' => 'CANTEEN_SPEND',
            't.school_id' => $currentSchoolId,
        ]);

    if ($searchQuery !== '') {
        $like = '%' . strtr($searchQuery, ['%' => '\%', '_' => '\_']) . '%';
        $query->andWhere(['or',
            ['ilike', 'student.name', $like, false],
            ['ilike', 'school.name', $like, false],
            ['ilike', 'pos_devices.label', $like, false],
            ['ilike', 'pos_devices.device_uid', $like, false],
        ]);
    }

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 25],
        'sort' => [
            'attributes' => [
                'created_at' => [
                    'asc' => ['t.created_at' => SORT_ASC],
                    'desc' => ['t.created_at' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
                'amount' => [
                    'asc' => ['t.amount' => SORT_ASC],
                    'desc' => ['t.amount' => SORT_DESC],
                ],
            ],
            'defaultOrder' => ['created_at' => SORT_DESC],
        ],
    ]);

    return $this->render('canteen-transactions', [
        'dataProvider' => $dataProvider,
        'searchQuery' => $searchQuery,
        'currentSchoolId' => $currentSchoolId,
    ]);
}

public function actionDeviceLookup()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $uid = trim((string) Yii::$app->request->get('device_uid'));

    if (empty($uid)) {
        return ['success' => false];
    }

    $device = \app\models\PosDevices::findOne(['device_uid' => $uid]);
    if (!$device || $device->status !== 'ACTIVE') {
        return ['success' => false];
    }

    return [
        'success' => true,
        'label' => $device->label ?: $device->device_uid,
    ];
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
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'SUPER_ADMIN') {
            return $this->redirect(['site/login']);
        }

        $schools = Schools::find()->orderBy(['name' => SORT_ASC])->all();
        return $this->render('super_admin', ['schools' => $schools]);
    }

    /**
     * Alias used by sidebar link site/school-registry.
     * Renders the same registry list as super-admin (use school-registry.php or super_admin.php).
     */
    public function actionSchoolRegistry()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'SUPER_ADMIN') {
            return $this->redirect(['site/login']);
        }

        $schools = Schools::find()->orderBy(['name' => SORT_ASC])->all();
        // Prefer dedicated school-registry view if present; fall back to super_admin.
        $viewFile = Yii::getAlias('@app/views/site/school-registry.php');
        if (is_file($viewFile)) {
            return $this->render('school-registry', ['schools' => $schools]);
        }
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
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN','SUPER_ADMIN'])) {
        Yii::$app->session->setFlash('error', 'Unauthorized administrative access level clearance.');
        return $this->redirect(['site/login']);
    }

    $userSchoolId = $this->requireWorkingSchoolId();
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

        // Sex — whitelist against known values so nothing unexpected lands in the column.
        $sex = strtoupper(trim($postData['sex'] ?? ''));
        $studentModel->sex = in_array($sex, ['MALE', 'FEMALE'], true) ? $sex : null;

        // Date of birth — validate it's a real date before saving.
        $dob = trim($postData['date_of_birth'] ?? '');
        if ($dob !== '' && \DateTime::createFromFormat('Y-m-d', $dob) !== false) {
            $studentModel->date_of_birth = $dob;
        } else {
            $studentModel->date_of_birth = null;
        }

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

        // Student photo — optional, converted to base64 same as the Settings page.
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('student_photo');
        if ($uploadedFile !== null) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSizeBytes = 2 * 1024 * 1024;

            if (!in_array($uploadedFile->type, $allowedTypes, true)) {
                Yii::$app->session->setFlash('error', 'Student photo must be a JPG, PNG, or WEBP image.');
                return $this->render('register_student', [
                    'model' => $studentModel,
                    'schoolName' => $school->name,
                    'baseFees' => $school->base_tuition_fees,
                ]);
            }

            if ($uploadedFile->size > $maxSizeBytes) {
                Yii::$app->session->setFlash('error', 'Student photo must be under 2MB.');
                return $this->render('register_student', [
                    'model' => $studentModel,
                    'schoolName' => $school->name,
                    'baseFees' => $school->base_tuition_fees,
                ]);
            }

            $imageData = file_get_contents($uploadedFile->tempName);
            $studentModel->profile_photo = 'data:' . $uploadedFile->type . ';base64,' . base64_encode($imageData);
        }

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
        $userSchoolId = $this->requireWorkingSchoolId();
        
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
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'], true)) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $userSchoolId = $this->requireWorkingSchoolId();
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
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'], true)) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $userSchoolId = $this->requireWorkingSchoolId();
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
       if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
            Yii::$app->session->setFlash('error', 'Unauthorized administrative access level clearance.');
            return $this->redirect(['site/login']);
        }

        $userSchoolId = $this->requireWorkingSchoolId();

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
       $identity = Yii::$app->user->identity;

    if (Yii::$app->user->isGuest || !in_array($identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'], true)) {
        throw new \yii\web\ForbiddenHttpException();}

        $userSchoolId = $this->requireWorkingSchoolId();

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
           $identity = Yii::$app->user->identity;

    if (Yii::$app->user->isGuest || !in_array($identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'], true)) {
        throw new \yii\web\ForbiddenHttpException();
        }

        $userSchoolId = $this->requireWorkingSchoolId();
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
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['TEACHER', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $teacherId = Yii::$app->user->identity->id;
        $schoolId = $this->requireWorkingSchoolId();
        $request = Yii::$app->request;

        $assignments = Yii::$app->db->createCommand(
            'SELECT id, class_level, subject_name FROM teacher_assignments WHERE teacher_id = :tid AND school_id = :sid'
        )->bindValues([':tid' => $teacherId, ':sid' => $schoolId])->queryAll();

        $selectedAssignmentId = (int)$request->get('assignment_id', 0);
        $selectedTerm = $request->get('term', 'TERM_1');
        $academicYear = (int)date('Y');

        $activeAssignment = null;
        $studentsList = [];
        $existingMarks = [];

        if (!empty($assignments)) {
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

            $marksRows = Yii::$app->db->createCommand(
                'SELECT student_id, bot_mark, mot_mark, eot_mark, teacher_comment, dos_feedback, bot_status, mot_status, eot_status 
                 FROM academic_marks 
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


        return $this->render('teacher_grading', [
            'assignments' => $assignments,
            'activeAssignment' => $activeAssignment,
            'selectedTerm' => $selectedTerm,
            'studentsList' => $studentsList,
            'existingMarks' => $existingMarks,
        ]);
    }
    
    }
public function actionSubmitMarks()
{
    if (Yii::$app->request->isPost && !Yii::$app->user->isGuest) {
        $user = Yii::$app->user->identity;
        if (!in_array($user->role, ['TEACHER', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $schoolId = $this->requireWorkingSchoolId();
        $postData = Yii::$app->request->post();

        $classLevel = $postData['class_level'] ?? '';
        $subjectName = $postData['subject_name'] ?? '';
        $term = $postData['term'] ?? 'TERM_1';
        $submissionMode = $postData['submission_mode'] ?? 'SUBMIT_ALL';
        $selectedStudents = $postData['selected_students'] ?? [];
        $academicYear = (int)date('Y');

        $scoresMatrix = $postData['Scores'] ?? [];

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($scoresMatrix as $studentId => $marks) {
                if ($submissionMode === 'SUBMIT_SELECTED' && !in_array((string)$studentId, $selectedStudents)) {
                    continue;
                }

                $bot = min(100, max(0, (float)($marks['bot'] ?? 0)));
                $mot = min(100, max(0, (float)($marks['mot'] ?? 0)));
                $eot = min(100, max(0, (float)($marks['eot'] ?? 0)));
                $comment = trim($marks['comment'] ?? '');

                $botTouched = ($marks['bot_touched'] ?? '0') === '1';
                $motTouched = ($marks['mot_touched'] ?? '0') === '1';
                $eotTouched = ($marks['eot_touched'] ?? '0') === '1';

                $exists = Yii::$app->db->createCommand(
                    'SELECT id, bot_mark, mot_mark, eot_mark, bot_status, mot_status, eot_status FROM academic_marks 
                     WHERE student_id = :st AND subject_name = :sub AND term = :trm AND academic_year = :yr'
                )->bindValues([':st' => $studentId, ':sub' => $subjectName, ':trm' => $term, ':yr' => $academicYear])->queryOne();

                if ($exists) {
                    $canEditBot = ($user->role === 'SCHOOL_ADMIN' || $exists['bot_status'] !== 'APPROVED_SEALED');
                    $canEditMot = ($user->role === 'SCHOOL_ADMIN' || $exists['mot_status'] !== 'APPROVED_SEALED');
                    $canEditEot = ($user->role === 'SCHOOL_ADMIN' || $exists['eot_status'] !== 'APPROVED_SEALED');

                    $updateFields = ['teacher_comment' => $comment, 'updated_at' => date('Y-m-d H:i:s')];

                    $shouldSubmitBot = $canEditBot && $botTouched && (abs(((float)$exists['bot_mark']) - $bot) > 0.0001 || $exists['bot_status'] === 'REJECTED_AMEND' || in_array($exists['bot_status'], [null, '', 'NOT_SUBMITTED']));
                    $shouldSubmitMot = $canEditMot && $motTouched && (abs(((float)$exists['mot_mark']) - $mot) > 0.0001 || $exists['mot_status'] === 'REJECTED_AMEND' || in_array($exists['mot_status'], [null, '', 'NOT_SUBMITTED']));
                    $shouldSubmitEot = $canEditEot && $eotTouched && (abs(((float)$exists['eot_mark']) - $eot) > 0.0001 || $exists['eot_status'] === 'REJECTED_AMEND' || in_array($exists['eot_status'], [null, '', 'NOT_SUBMITTED']));

                    if ($shouldSubmitBot) { $updateFields['bot_mark'] = $bot; $updateFields['bot_status'] = 'PENDING_REVIEW'; }
                    if ($shouldSubmitMot) { $updateFields['mot_mark'] = $mot; $updateFields['mot_status'] = 'PENDING_REVIEW'; }
                    if ($shouldSubmitEot) { $updateFields['eot_mark'] = $eot; $updateFields['eot_status'] = 'PENDING_REVIEW'; }

                    Yii::$app->db->createCommand()->update('academic_marks', $updateFields, ['id' => $exists['id']])->execute();
                } else {
                    Yii::$app->db->createCommand()->insert('academic_marks', [
                        'school_id' => $schoolId,
                        'student_id' => (int)$studentId,
                        'subject_name' => $subjectName,
                        'class_level' => $classLevel,
                        'term' => $term,
                        'academic_year' => $academicYear,
                        'bot_mark' => $botTouched ? $bot : 0,
                        'mot_mark' => $motTouched ? $mot : 0,
                        'eot_mark' => $eotTouched ? $eot : 0,
                        'teacher_comment' => $comment,
                        'bot_status' => $botTouched ? 'PENDING_REVIEW' : 'NOT_SUBMITTED',
                        'mot_status' => $motTouched ? 'PENDING_REVIEW' : 'NOT_SUBMITTED',
                        'eot_status' => $eotTouched ? 'PENDING_REVIEW' : 'NOT_SUBMITTED',
                        'updated_at' => date('Y-m-d H:i:s')
                    ])->execute();
                }
            }

            $dbTransaction->commit();
            Yii::$app->session->setFlash('success', "Marks added to Dos Queue.");
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
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN', 'SCHOOL_ADMIN'])) {
        return $this->redirect(['site/login']);
    }

    $schoolId = $this->requireWorkingSchoolId();
    $request = Yii::$app->request;

    $selectedClass   = $request->get('class_level', 'Senior 1');
    $selectedTerm    = $request->get('term', 'TERM_1');
    $selectedSubject = $request->get('subject', '');           
    $onlyPending     = $request->get('status') === 'pending'; 
    $pageSize        = (int) $request->get('per_page', 20);
    $pageSize        = in_array($pageSize, [25, 50, 100], true) ? $pageSize : 20;
    $academicYear    = (int) date('Y');

    $db = Yii::$app->db;

    $marksSummary = $db->createCommand(
        "SELECT subject_name,
                COUNT(id) AS total_entries,
                SUM(CASE WHEN bot_status = 'PENDING_REVIEW' OR mot_status = 'PENDING_REVIEW' OR eot_status = 'PENDING_REVIEW' THEN 1 ELSE 0 END) AS pending_rows,
                SUM(CASE WHEN bot_status = 'REJECTED_AMEND' OR mot_status = 'REJECTED_AMEND' OR eot_status = 'REJECTED_AMEND' THEN 1 ELSE 0 END) AS rejected_rows,
                SUM(CASE WHEN bot_status = 'APPROVED_SEALED' OR mot_status = 'APPROVED_SEALED' OR eot_status = 'APPROVED_SEALED' THEN 1 ELSE 0 END) AS sealed_rows
         FROM academic_marks
         WHERE school_id = :sid AND class_level = :cls AND term = :trm AND academic_year = :yr
         GROUP BY subject_name
         ORDER BY subject_name ASC"
    )->bindValues([
        ':sid' => $schoolId, ':cls' => $selectedClass, ':trm' => $selectedTerm, ':yr' => $academicYear,
    ])->queryAll();

    $subjectList = [];
    $pendingBySubject = [];
    foreach ($marksSummary as $row) {
        $subjectList[] = $row['subject_name'];
        $pendingBySubject[$row['subject_name']] = (int) $row['pending_rows'];
    }
    $pendingTotal = $selectedSubject !== ''
        ? ($pendingBySubject[$selectedSubject] ?? 0)
        : array_sum($pendingBySubject);

    // ---- Filtered WHERE clause shared by the count query and the page query ----
    $where = 'm.school_id = :sid AND m.class_level = :cls AND m.term = :trm AND m.academic_year = :yr';
    $params = [':sid' => $schoolId, ':cls' => $selectedClass, ':trm' => $selectedTerm, ':yr' => $academicYear];

    if ($selectedSubject !== '') {
        $where .= ' AND m.subject_name = :subj';
        $params[':subj'] = $selectedSubject;
    }

    if ($onlyPending) {
        $where .= " AND (m.bot_status = 'PENDING_REVIEW' OR m.mot_status = 'PENDING_REVIEW' OR m.eot_status = 'PENDING_REVIEW')";
    }

    $totalCount = (int) $db->createCommand(
        "SELECT COUNT(*) FROM academic_marks m WHERE $where"
    )->bindValues($params)->queryScalar();

    $pagination = new \yii\data\Pagination([
        'totalCount'    => $totalCount,
        'pageSize'      => $pageSize,
        'pageSizeParam' => 'per_page',
        'params'        => array_filter([
            'class_level' => $selectedClass,
            'term'        => $selectedTerm,
            'subject'     => $selectedSubject,
            'status'      => $onlyPending ? 'pending' : null,
            'per_page'    => $pageSize,
        ]),
    ]);

 
    $rawRecords = $db->createCommand(
        "SELECT m.*, s.name as student_name
         FROM academic_marks m
         JOIN students s ON m.student_id = s.id
         WHERE $where
         ORDER BY m.subject_name ASC, s.name ASC
         LIMIT " . (int) $pagination->limit . " OFFSET " . (int) $pagination->offset
    )->bindValues($params)->queryAll();

    return $this->render('dos_review', [
        'marksSummary'    => $marksSummary,
        'rawRecords'      => $rawRecords,
        'selectedClass'   => $selectedClass,
        'selectedTerm'    => $selectedTerm,
        'selectedSubject' => $selectedSubject,
        'onlyPending'     => $onlyPending,
        'subjectList'     => $subjectList,
        'pagination'      => $pagination,
        'pendingTotal'    => $pendingTotal,
    ]);
}
   
    public function actionSealMarks()
    {
        if (Yii::$app->request->isPost && !Yii::$app->user->isGuest && in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN','SCHOOL_ADMIN'])) {
            $schoolId = $this->requireWorkingSchoolId();
            $classLevel = Yii::$app->request->post('class_level');
            $subjectName = Yii::$app->request->post('subject_name');
            $term = Yii::$app->request->post('term');
            $academicYear = (int)date('Y');

            $pendingCondition = ['or',
                ['bot_status' => 'PENDING_REVIEW'],
                ['mot_status' => 'PENDING_REVIEW'],
                ['eot_status' => 'PENDING_REVIEW'],
            ];

            Yii::$app->db->createCommand()->update('academic_marks', [
                'bot_status' => new \yii\db\Expression("CASE WHEN bot_status = 'PENDING_REVIEW' THEN 'APPROVED_SEALED' ELSE bot_status END"),
                'mot_status' => new \yii\db\Expression("CASE WHEN mot_status = 'PENDING_REVIEW' THEN 'APPROVED_SEALED' ELSE mot_status END"),
                'eot_status' => new \yii\db\Expression("CASE WHEN eot_status = 'PENDING_REVIEW' THEN 'APPROVED_SEALED' ELSE eot_status END"),
                'dos_feedback' => null,
            ], ['and', [
                'school_id' => $schoolId,
                'class_level' => $classLevel,
                'subject_name' => $subjectName,
                'term' => $term,
                'academic_year' => $academicYear,
            ], $pendingCondition])->execute();

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

        $schoolId = $this->requireWorkingSchoolId();

        $teachers = User::find()
            ->where(['school_id' => $schoolId, 'role' => 'TEACHER'])
            ->orderBy(['username' => SORT_ASC])
            ->all();

        $activeAssignments = Yii::$app->db->createCommand(
            'SELECT a.*, u.username as teacher_name 
             FROM teacher_assignments a
             JOIN system_admins u ON a.teacher_id = u.id
             WHERE a.school_id = :sid
             ORDER BY u.username ASC, a.class_level ASC'
        )->bindValue(':sid', $schoolId)->queryAll();

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
            $schoolId = $this->requireWorkingSchoolId();
            
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
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['DOS', 'SUPER_ADMIN', 'SCHOOL_ADMIN'])) {
            return $this->redirect(['site/login']);
        }

        $schoolId = $this->requireWorkingSchoolId();
        $request = Yii::$app->request;
        
        $selectedClass = $request->get('class_level', 'Senior 1');
        $selectedTerm = $request->get('term', 'TERM_1'); 

        $students = \app\models\Students::find()
            ->where(['school_id' => $schoolId, 'class_level' => $selectedClass, 'status' => 'ACTIVE'])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $this->render('print_reports', [
            'students' => $students,
            'selectedClass' => $selectedClass,
            'selectedTerm' => $selectedTerm, 
        ]);
    }


public function actionViewReportCard($id, $term = 'TERM_1')
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }

    $schoolId = $this->requireWorkingSchoolId();
    $academicYear = (int)date('Y');

    $student = Students::findOne(['id' => $id, 'school_id' => $schoolId]);
    if (!$student) {
        throw new \yii\web\NotFoundHttpException("Target student record profile file not found.");
    }

    $gradesList = Yii::$app->db->createCommand(
        'SELECT * FROM academic_marks 
         WHERE student_id = :sid AND term = :trm AND academic_year = :yr'
    )->bindValues([':sid' => $id, ':trm' => $term, ':yr' => $academicYear])->queryAll();

    $classPosition = null;
    $classSize = null;
    if (strpos($student->class_level, 'Primary') !== false) {
        [$classPosition, $classSize] = $this->computePrimaryClassRanking(
            $schoolId, $student->class_level, $term, $academicYear, (int)$student->id
        );
    }

    return $this->render('view_report_card', [
        'student' => $student,
        'gradesList' => $gradesList,
        'term' => $term,
        'year' => $academicYear,
        'classPosition' => $classPosition,
        'classSize' => $classSize,
    ]);
}

//ranking
private function computePrimaryClassRanking(int $schoolId, string $classLevel, string $term, int $academicYear, int $studentId): array
{
    $primaryCoreSubjects = ['English', 'Mathematics', 'Science', 'Social Studies'];

    $subjectParams = [];
    $subjectPlaceholders = [];
    foreach ($primaryCoreSubjects as $i => $subjectName) {
        $key = ':subj' . $i;
        $subjectParams[$key] = $subjectName;
        $subjectPlaceholders[] = $key;
    }
    $placeholders = implode(',', $subjectPlaceholders);

    $rows = Yii::$app->db->createCommand(
        "SELECT m.student_id,
                SUM(
                    CASE
                        WHEN m.eot_mark > 0 THEN m.eot_mark
                        WHEN m.mot_mark > 0 THEN m.mot_mark
                        ELSE m.bot_mark
                    END
                ) AS total_marks
         FROM academic_marks m
         WHERE m.school_id = :sid AND m.class_level = :cls AND m.term = :trm AND m.academic_year = :yr
           AND m.subject_name IN ($placeholders)
         GROUP BY m.student_id"
    )->bindValues(array_merge(
        [':sid' => $schoolId, ':cls' => $classLevel, ':trm' => $term, ':yr' => $academicYear],
        $subjectParams
    ))->queryAll();

    // Rank by total marks, descending — highest score is position 1.
    usort($rows, fn($a, $b) => $b['total_marks'] <=> $a['total_marks']);

    $classSize = count($rows);
    $position = null;

    foreach ($rows as $i => $row) {
        if ((int)$row['student_id'] === $studentId) {
            $position = $i + 1;
            break;
        }
    }

    return [$position, $classSize];
}

      
    public function actionRejectMarks()
    {
        if (Yii::$app->request->isPost && !Yii::$app->user->isGuest && in_array(Yii::$app->user->identity->role, ['DOS','SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
            $schoolId = $this->requireWorkingSchoolId();
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

            $pendingCondition = ['or',
                ['bot_status' => 'PENDING_REVIEW'],
                ['mot_status' => 'PENDING_REVIEW'],
                ['eot_status' => 'PENDING_REVIEW'],
            ];

            Yii::$app->db->createCommand()->update('academic_marks', [
                'bot_status' => new \yii\db\Expression("CASE WHEN bot_status = 'PENDING_REVIEW' THEN 'REJECTED_AMEND' ELSE bot_status END"),
                'mot_status' => new \yii\db\Expression("CASE WHEN mot_status = 'PENDING_REVIEW' THEN 'REJECTED_AMEND' ELSE mot_status END"),
                'eot_status' => new \yii\db\Expression("CASE WHEN eot_status = 'PENDING_REVIEW' THEN 'REJECTED_AMEND' ELSE eot_status END"),
                'dos_feedback' => $dosComment,
            ], ['and', [
                'school_id' => $schoolId,
                'class_level' => $classLevel,
                'subject_name' => $subjectName,
                'term' => $term,
                'academic_year' => $academicYear,
            ], $pendingCondition])->execute();
            Yii::$app->session->setFlash('success', "Marks sheet for {$classLevel} — {$subjectName} has been rejected back to the teacher.");
            return $this->redirect(['site/dos-review', 'class_level' => $classLevel, 'term' => $term]);
        }
        throw new \yii\web\ForbiddenHttpException();
    }


    public function actionSchoolAdmin()
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['SCHOOL_ADMIN', 'SUPER_ADMIN'], true)) {
            return $this->redirect(['site/login']);
        }

        $schoolId = $this->requireWorkingSchoolId();
        $school = Schools::findOne($schoolId);

        if (!$school) {
            throw new \yii\web\NotFoundHttpException("Institution configuration matrix corrupted.");
        }

        $stats = [
            'total_collected' => (float) Transactions::find()->joinWith('student')->where(['transaction_type' => 'TUITION', 'students.school_id' => $schoolId])->sum('amount'),
            'total_outstanding' => (float) Students::find()->where(['school_id' => $schoolId])->sum('tuition_balance'),
            'active_students' => (int) Students::find()->where(['school_id' => $schoolId, 'status' => 'ACTIVE'])->count(),
            'total_staff' => (int) User::find()->where(['school_id' => $schoolId])->count(),
        ];

                $pendingSheets = Yii::$app->db->createCommand(
                        "SELECT subject_name, class_level, term, COUNT(id) as student_count
                         FROM academic_marks
                         WHERE school_id = :sid
                             AND (bot_status = 'PENDING_REVIEW' OR mot_status = 'PENDING_REVIEW' OR eot_status = 'PENDING_REVIEW')
                         GROUP BY subject_name, class_level, term
                         LIMIT 5"
                )->bindValue(':sid', $schoolId)->queryAll();

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

public function actionBulkModerateMarks()
{
    if (!Yii::$app->request->isPost || Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['DOS', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $schoolId = $this->requireWorkingSchoolId();
    $userRole = Yii::$app->user->identity->role;
    $request = Yii::$app->request;

    $classLevel = $request->post('class_level');
    $term = $request->post('term');
    $operation = $request->post('moderation_action');
    $dosComment = trim($request->post('dos_comment', ''));
    $academicYear = (int)date('Y');

    $validColumns = ['bot', 'mot', 'eot'];

    $selectedTokens = $request->post('selected_marks', []);
    $unsealTokens = $request->post('selected_unseal', []);

    if (in_array($operation, ['REJECT_SELECTED', 'REJECT_ALL', 'ADMIN_UNSEAL_SELECTED']) && empty($dosComment)) {
        Yii::$app->session->setFlash('error', 'Operational Block: You must provide a correction comment for this action.');
        return $this->redirect(['site/dos-review', 'class_level' => $classLevel, 'term' => $term]);
    }

    if ($operation === 'ADMIN_UNSEAL_SELECTED' && !in_array($userRole, ['SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $dbTransaction = Yii::$app->db->beginTransaction();
    try {
        switch ($operation) {

            case 'APPROVE_SELECTED':
            case 'REJECT_SELECTED':
                if (empty($selectedTokens)) {
                    Yii::$app->session->setFlash('error', 'No student columns selected. Please check boxes to moderate.');
                    break;
                }
                $newStatus = $operation === 'APPROVE_SELECTED' ? 'APPROVED_SEALED' : 'REJECTED_AMEND';
                $touched = 0;

                foreach ($selectedTokens as $token) {
                    [$recordId, $col] = array_pad(explode('_', (string)$token, 2), 2, null);
                    if (!in_array($col, $validColumns) || !ctype_digit((string)$recordId)) {
                        continue;
                    }

                    $update = ["{$col}_status" => $newStatus];
                    if ($operation === 'REJECT_SELECTED') {
                        $update['dos_feedback'] = $dosComment;
                    } else {
                        $update['dos_feedback'] = null;
                    }

                    $affected = Yii::$app->db->createCommand()->update('academic_marks', $update, [
                        'id' => $recordId,
                        'school_id' => $schoolId,
                        "{$col}_status" => 'PENDING_REVIEW', // only ever moves a column OUT of pending — siblings untouched
                    ])->execute();
                    $touched += $affected;
                }

                Yii::$app->session->setFlash('success', "{$touched} mark column(s) " . ($operation === 'APPROVE_SELECTED' ? 'approved and sealed.' : 'returned to the teacher for revision.'));
                break;

            case 'APPROVE_ALL':
            case 'REJECT_ALL':
                $newStatus = $operation === 'APPROVE_ALL' ? 'APPROVED_SEALED' : 'REJECTED_AMEND';
                $totalTouched = 0;

                foreach ($validColumns as $col) {
                    $update = ["{$col}_status" => $newStatus];
                    $update['dos_feedback'] = $operation === 'REJECT_ALL' ? $dosComment : null;

                    $affected = Yii::$app->db->createCommand()->update('academic_marks', $update, [
                        'school_id' => $schoolId,
                        'class_level' => $classLevel,
                        'term' => $term,
                        'academic_year' => $academicYear,
                        "{$col}_status" => 'PENDING_REVIEW',
                    ])->execute();
                    $totalTouched += $affected;
                }

                Yii::$app->session->setFlash('success', "{$totalTouched} pending mark column(s) for {$classLevel} " . ($operation === 'APPROVE_ALL' ? 'approved and sealed.' : 'returned for revision.'));
                break;

            case 'ADMIN_UNSEAL_SELECTED':
                if (empty($unsealTokens)) {
                    Yii::$app->session->setFlash('error', 'No sealed columns selected to unseal.');
                    break;
                }
                $unsealedCount = 0;

                foreach ($unsealTokens as $token) {
                    [$recordId, $col] = array_pad(explode('_', (string)$token, 2), 2, null);
                    if (!in_array($col, $validColumns) || !ctype_digit((string)$recordId)) {
                        continue;
                    }

                    $affected = Yii::$app->db->createCommand()->update('academic_marks', [
                        "{$col}_status" => 'REJECTED_AMEND',
                        'dos_feedback' => '[ADMIN CORRECTION] ' . $dosComment,
                    ], [
                        'id' => $recordId,
                        'school_id' => $schoolId,
                        "{$col}_status" => 'APPROVED_SEALED', // only reverses a genuinely sealed column
                    ])->execute();
                    $unsealedCount += $affected;
                }

                Yii::$app->session->setFlash('success', "{$unsealedCount} sealed column(s) unsealed and returned to the teacher.");
                break;

            default:
                Yii::$app->session->setFlash('error', 'Unknown moderation action.');
        }

        $dbTransaction->commit();
    } catch (\Exception $e) {
        $dbTransaction->rollBack();
        Yii::$app->session->setFlash('error', "Moderation batch engine crash: " . $e->getMessage());
    }

    return $this->redirect(['site/dos-review', 'class_level' => $classLevel, 'term' => $term]);
}


public function actionBatchPrintReports($class_level, $term = 'TERM_1')
{
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['DOS', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        return $this->redirect(['site/login']);
    }

    $schoolId = $this->requireWorkingSchoolId();
    $academicYear = (int)date('Y');

    $students = \app\models\Students::find()
        ->where(['school_id' => $schoolId, 'class_level' => $class_level, 'status' => 'ACTIVE'])
        ->andWhere(['<=', 'tuition_balance', 0])
        ->orderBy(['name' => SORT_ASC])
        ->all();

    if (empty($students)) {
        Yii::$app->session->setFlash('error', 'Batch Operation Cancelled: No fully paid, cleared student records found in this class.');
        return $this->redirect(['site/print-reports', 'class_level' => $class_level]);
    }

    $batchGrades = [];
    foreach ($students as $st) {
        $batchGrades[$st->id] = Yii::$app->db->createCommand(
            'SELECT * FROM academic_marks WHERE student_id = :sid AND term = :trm AND academic_year = :yr'
        )->bindValues([':sid' => $st->id, ':trm' => $term, ':yr' => $academicYear])->queryAll();
    }

    $classPositionMap = [];
    $classSize = null;
    if (strpos($class_level, 'Primary') !== false) {
        [$classPositionMap, $classSize] = $this->computePrimaryClassRankingMap(
            $schoolId, $class_level, $term, $academicYear
        );
    }

    return $this->renderPartial('batch_print_reports', [
        'students' => $students,
        'batchGrades' => $batchGrades,
        'term' => $term,
        'year' => $academicYear,
        'classLevel' => $class_level,
        'classPositionMap' => $classPositionMap,
        'classSize' => $classSize,
    ]);
}

//ranking
private function computePrimaryClassRankingMap(int $schoolId, string $classLevel, string $term, int $academicYear): array
{
    $primaryCoreSubjects = ['English', 'Mathematics', 'Science', 'Social Studies']; // ADJUST

    $subjectParams = [];
    $subjectPlaceholders = [];
    foreach ($primaryCoreSubjects as $i => $subjectName) {
        $key = ':subj' . $i;
        $subjectParams[$key] = $subjectName;
        $subjectPlaceholders[] = $key;
    }
    $placeholders = implode(',', $subjectPlaceholders);

    $rows = Yii::$app->db->createCommand(
        "SELECT m.student_id,
                SUM(
                    CASE
                        WHEN m.eot_mark > 0 THEN m.eot_mark
                        WHEN m.mot_mark > 0 THEN m.mot_mark
                        ELSE m.bot_mark
                    END
                ) AS total_marks
         FROM academic_marks m
         WHERE m.school_id = :sid AND m.class_level = :cls AND m.term = :trm AND m.academic_year = :yr
           AND m.subject_name IN ($placeholders)
         GROUP BY m.student_id"
    )->bindValues(array_merge(
        [':sid' => $schoolId, ':cls' => $classLevel, ':trm' => $term, ':yr' => $academicYear],
        $subjectParams
    ))->queryAll();

    usort($rows, fn($a, $b) => $b['total_marks'] <=> $a['total_marks']);

    $classSize = count($rows);
    $positionMap = [];
    foreach ($rows as $i => $row) {
        $positionMap[(int)$row['student_id']] = $i + 1;
    }

    return [$positionMap, $classSize];
}
    public function actionExportClassMarks($class_level, $term = 'TERM_1')
    {
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'DOS', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $schoolId = $this->requireWorkingSchoolId();
        $academicYear = (int)date('Y');

        $records = Yii::$app->db->createCommand(
            "SELECT s.name, s.payment_code, m.subject_name, m.bot_mark, m.mot_mark, m.eot_mark, m.teacher_comment
             FROM academic_marks m
             JOIN students s ON m.student_id = s.id
             WHERE m.school_id = :sid AND m.class_level = :cls AND m.term = :trm AND m.academic_year = :yr
             ORDER BY s.name ASC, m.subject_name ASC"
        )->bindValues([':sid' => $schoolId, ':cls' => $class_level, ':trm' => $term, ':yr' => $academicYear])->queryAll();

        $fileName = str_replace(' ', '_', $class_level) . "_{$term}_Marks_Ledger.csv";
        
        Yii::$app->response->getHeaders()
            ->set('Content-Type', 'text/csv; charset=utf-8')
            ->set('Content-Disposition', "attachment; filename={$fileName}");

        $outputBuffer = fopen('php://output', 'w');
        fputcsv($outputBuffer, ['Student Full Name', 'Payment Code', 'Subject Course', 'BOT (20%)', 'MOT (30%)', 'EOT (50%)', 'Teacher Comment']);

        foreach ($records as $row) {
            fputcsv($outputBuffer, [
                $row['name'], $row['payment_code'], $row['subject_name'], 
                $row['bot_mark'], $row['mot_mark'], $row['eot_mark'], $row['teacher_comment']
            ]);
        }
        fclose($outputBuffer);
        exit();
    }


 
public function actionSettings()
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }

    $user = Yii::$app->user->identity;

    if (Yii::$app->request->isPost) {
        $newUsername = trim((string) Yii::$app->request->post('username', ''));

        if ($newUsername === '') {
            Yii::$app->session->setFlash('error', 'Username cannot be empty.');
            return $this->redirect(['site/settings']);
        }

        if (!preg_match('/^[a-zA-Z0-9_.]{3,32}$/', $newUsername)) {
            Yii::$app->session->setFlash('error', 'Username must be 3-32 characters: letters, numbers, dots, or underscores only.');
            return $this->redirect(['site/settings']);
        }

        $usernameTaken = $user::find()
            ->where(['username' => $newUsername])
            ->andWhere(['!=', 'id', $user->id])
            ->exists();

        if ($usernameTaken) {
            Yii::$app->session->setFlash('error', 'That username is already taken.');
            return $this->redirect(['site/settings']);
        }

        $user->username = $newUsername;

        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('profile_photo');
        if ($uploadedFile !== null) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSizeBytes = 2 * 1024 * 1024; // 2MB cap 

            if (!in_array($uploadedFile->type, $allowedTypes, true)) {
                Yii::$app->session->setFlash('error', 'Profile picture must be a JPG, PNG, or WEBP image.');
                return $this->redirect(['site/settings']);
            }

            if ($uploadedFile->size > $maxSizeBytes) {
                Yii::$app->session->setFlash('error', 'Profile picture must be under 2MB.');
                return $this->redirect(['site/settings']);
            }

            $imageData = file_get_contents($uploadedFile->tempName);
            $user->profile_photo = 'data:' . $uploadedFile->type . ';base64,' . base64_encode($imageData);
        }

        if ($user->save(false)) {
            Yii::$app->session->setFlash('success', 'Settings updated successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update settings.');
        }

        return $this->redirect(['site/settings']);
    }

    // Read-only view of a teacher's assigned classes/subjects 
    $assignments = [];
    if (($user->role ?? null) === 'TEACHER') {
        $assignments = \app\models\TeacherAssignment::find()
            ->where(['teacher_id' => $user->id, 'school_id' => $user->school_id])
            ->orderBy(['class_level' => SORT_ASC, 'subject_name' => SORT_ASC])
            ->all();
    }

    return $this->render('settings', [
        'user' => $user,
        'assignments' => $assignments,
    ]);
}

public function actionWalletAdjust()
{
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $studentId = Yii::$app->request->post('student_id');

    if (empty($studentId)) {
        Yii::$app->session->setFlash('error', 'No student selected — please search and pick a student first.');
        return $this->redirect(['site/bursar']);
    }

    $student = Students::findOne(['id' => $studentId, 'school_id' => $this->requireWorkingSchoolId()]);
    if (!$student) {
        Yii::$app->session->setFlash('error', 'Student not found.');
        return $this->redirect(['site/bursar']);
    }

    $action = Yii::$app->request->post('wallet_action');
    $amount = (float) Yii::$app->request->post('amount', 0);

    $transaction = Yii::$app->db->beginTransaction();
    try {
        if ($action === 'TOPUP' && $amount > 0) {
            $student->swallet_balance += $amount;
            $student->save(false);

            $tx = new Transactions();
            $tx->student_id = $student->id;
            $tx->transaction_type = 'POCKET_MONEY';
            $tx->payment_channel = 'OVER_THE_COUNTER';
            $tx->amount = $amount;
            $tx->external_reference = 'MANUAL-' . strtoupper(uniqid());
            $tx->created_by = Yii::$app->user->id;
            $tx->save(false);
        } elseif ($action === 'FREEZE') {
            $student->wallet_frozen = true;
            $student->save(false);
        } elseif ($action === 'UNFREEZE') {
            $student->wallet_frozen = false;
            $student->save(false);
        }
        $transaction->commit();
        Yii::$app->session->setFlash('success', 'Wallet updated for ' . Html::encode($student->name) . '.');
    } catch (\Throwable $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'Wallet update failed: ' . $e->getMessage());
    }

    return $this->redirect(['site/bursar']);
}


public function actionVoidTransaction()
{
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $id = Yii::$app->request->post('id');

    if (empty($id)) {
        Yii::$app->session->setFlash('error', 'No transaction selected — please use the void icon on a transaction row.');
        return $this->redirect(['site/bursar']);
    }

    $tx = Transactions::findOne($id);
    if (!$tx || !$tx->student || (int) $tx->student->school_id !== (int) $this->requireWorkingSchoolId()) {
        Yii::$app->session->setFlash('error', 'Transaction not found.');
        return $this->redirect(['site/bursar']);
    }

    if ($tx->status === 'VOIDED') {
        Yii::$app->session->setFlash('error', 'This transaction has already been voided.');
        return $this->redirect(['site/bursar']);
    }

    $reason = trim(Yii::$app->request->post('void_reason', ''));
    if (empty($reason)) {
        Yii::$app->session->setFlash('error', 'A reason is required to void a transaction.');
        return $this->redirect(['site/bursar']);
    }

    $dbTransaction = Yii::$app->db->beginTransaction();
    try {
        if ($tx->transaction_type === 'TUITION') {
            $tx->student->tuition_balance += $tx->amount;
        } elseif ($tx->transaction_type === 'POCKET_MONEY') {
            $tx->student->swallet_balance -= $tx->amount;
        }
        $tx->student->save(false);

        $tx->status = 'VOIDED';
        $tx->void_reason = $reason;
        $tx->voided_by = Yii::$app->user->id;
        $tx->voided_at = date('Y-m-d H:i:s');
        $tx->save(false);

        $dbTransaction->commit();
        Yii::$app->session->setFlash('success', 'Transaction voided and reversed.');
    } catch (\Throwable $e) {
        $dbTransaction->rollBack();
        Yii::$app->session->setFlash('error', 'Void failed: ' . $e->getMessage());
    }

    return $this->redirect(['site/bursar']);
}

public function actionBatchInvoice()
{
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $schoolId = $this->requireWorkingSchoolId();

    if (Yii::$app->request->isPost) {
        $classLevel = trim(Yii::$app->request->post('class_level', ''));
        $baseFee = (float) Yii::$app->request->post('base_fee', 0);
        $applySiblingWaiver = (bool) Yii::$app->request->post('apply_sibling_waiver');
        $applyStaffWaiver = (bool) Yii::$app->request->post('apply_staff_waiver');

        if (empty($classLevel) || $baseFee <= 0) {
            Yii::$app->session->setFlash('error', 'Select a class and a valid base fee before generating invoices.');
            return $this->redirect(['site/bursar']);
        }

        $students = Students::find()->where(['school_id' => $schoolId, 'class_level' => $classLevel])->all();

        if (empty($students)) {
            Yii::$app->session->setFlash('error', 'No students found in that class.');
            return $this->redirect(['site/bursar']);
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($students as $student) {
                $fee = $baseFee;
                if ($applyStaffWaiver && $student->is_staff_child) {
                    $fee *= (1 - ($student->staff_waiver_pct ?: 0));
                }
                if ($applySiblingWaiver && $student->sibling_group_id && $student->sibling_rank > 1) {
                    $fee *= (1 - ($student->sibling_waiver_pct ?: 0));
                }
                $student->tuition_balance += $fee;
                $student->save(false);
            }
            $dbTransaction->commit();
            Yii::$app->session->setFlash('success', count($students) . ' students in ' . Html::encode($classLevel) . ' invoiced successfully.');
        } catch (\Throwable $e) {
            $dbTransaction->rollBack();
            Yii::$app->session->setFlash('error', 'Batch invoicing failed: ' . $e->getMessage());
        }
    }

    return $this->redirect(['site/bursar']);
}


public function actionForcePosSync()
{
    if (!Yii::$app->request->isPost) {
        throw new \yii\web\MethodNotAllowedHttpException('This action only accepts POST requests.');
    }

    $identity = Yii::$app->user->identity;
    $now = date('Y-m-d H:i:s');

    $condition = ['status' => 'ACTIVE'];

    // Prefer working school context (session for SUPER_ADMIN, identity for others).
    // If SUPER_ADMIN has no school selected, sync all active devices platform-wide.
    $workingSchoolId = $this->getWorkingSchoolId();
    if ($workingSchoolId !== null) {
        $condition['school_id'] = $workingSchoolId;
    } elseif ($identity->role !== 'SUPER_ADMIN') {
        $condition['school_id'] = $identity->school_id;
    }

    $updated = PosDevices::updateAll(['last_synced_at' => $now], $condition);

    Yii::$app->session->setFlash('success', "Sync signal sent to {$updated} active POS device(s) at " . date('H:i', strtotime($now)) . '.');

    return $this->redirect(Yii::$app->request->referrer ?? ['site/bursar']);
}


public function actionPrintReceipt($id)
{
    $tx = Transactions::findOne($id);
    if (!$tx || !$tx->student || (int) $tx->student->school_id !== (int) $this->requireWorkingSchoolId()) {
        throw new \yii\web\NotFoundHttpException('Transaction not found.');
    }

    return $this->renderPartial('receipt-print', ['tx' => $tx]);
}


public function actionExpenseClaims()
{
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $schoolId = $this->requireWorkingSchoolId();
    $pendingClaims = ExpenseClaims::find()->where(['school_id' => $schoolId, 'status' => 'PENDING'])->all();

    if (Yii::$app->request->isPost) {
        $claimId = Yii::$app->request->post('claim_id');
        $decision = Yii::$app->request->post('decision');
        $claim = ExpenseClaims::findOne(['id' => $claimId, 'school_id' => $schoolId]);

        if ($claim) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $claim->status = $decision === 'APPROVE' ? 'APPROVED' : 'REJECTED';
                $claim->reviewed_by = Yii::$app->user->id;
                $claim->reviewed_at = date('Y-m-d H:i:s');
                $claim->save(false);

                if ($decision === 'APPROVE') {
                    $tx = new Transactions();
                    $tx->student_id = null;
                    $tx->school_id = $schoolId;
                    $tx->transaction_type = 'EXPENSE';
                    $tx->payment_channel = 'PETTY_CASH';
                    $tx->amount = $claim->amount;
                    $tx->external_reference = 'EXPENSE-' . $claim->id . ($claim->reference_number ? ' / ' . $claim->reference_number : '');                    $tx->status = 'SUCCESS';
                    $tx->bank_settled = true; // cash paid out directly, nothing to settle
                    $tx->created_by = Yii::$app->user->id;
                    $tx->save(false);
                }

                $dbTransaction->commit();
                Yii::$app->session->setFlash('success', 'Claim ' . strtolower($claim->status) . '.');
            } catch (\Throwable $e) {
                $dbTransaction->rollBack();
                Yii::$app->session->setFlash('error', 'Could not process claim: ' . $e->getMessage());
            }
        }
        return $this->redirect(['site/expense-claims']);
    }

    return $this->render('expense-claims', ['pendingClaims' => $pendingClaims]);
}


public function actionExpenseClaimsCreate()
{
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    if (Yii::$app->request->isPost) {
        $schoolId = $this->requireWorkingSchoolId();

        $claim = new ExpenseClaims();
        $claim->school_id = $schoolId;
        $claim->category = Yii::$app->request->post('category');
        $claim->description = Yii::$app->request->post('description');
        $claim->amount = (float) Yii::$app->request->post('amount');
        $claim->status = 'PENDING';
        $claim->requested_by = Yii::$app->user->id;
        $claim->created_at = date('Y-m-d H:i:s');
        $claim->reference_number = trim(Yii::$app->request->post('reference_number', '')) ?: null;

        if ($claim->save()) {
            Yii::$app->session->setFlash('success', 'Expense claim submitted for review.');
        } else {
            Yii::$app->session->setFlash('error', 'Could not submit claim: ' . implode(' ', $claim->getFirstErrors()));
        }
    }

    return $this->redirect(['site/expense-claims']);
}

public function actionStudentsByClass()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $classLevel = Yii::$app->request->get('class_level');
    $search = trim(Yii::$app->request->get('q', ''));

    if (empty($classLevel)) {
        return [];
    }

    $query = Students::find()
        ->where(['school_id' => $this->requireWorkingSchoolId(), 'class_level' => $classLevel]);

    if (!empty($search)) {
        $query->andWhere(['ilike', 'name', $search]);
    }

    $students = $query->orderBy(['name' => SORT_ASC])->limit(50)->all();

    return array_map(function ($s) {
        return [
            'id' => $s->id,
            'text' => $s->name . ' — ' . $s->payment_code . ($s->wallet_frozen ? ' (Frozen)' : ''),
        ];
    }, $students);
}



public function actionClassList()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $classes = Students::find()
        ->select('class_level')
        ->distinct()
        ->where(['school_id' => $this->requireWorkingSchoolId()])
        ->andWhere(['is not', 'class_level', null])
        ->orderBy(['class_level' => SORT_ASC])
        ->column();

    return array_values(array_filter($classes));
}

public function actionClassStudentCount()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['BURSAR', 'SCHOOL_ADMIN', 'SUPER_ADMIN'])) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $classLevel = Yii::$app->request->get('class_level');
    if (empty($classLevel)) {
        return ['count' => 0];
    }

    $count = Students::find()
        ->where(['school_id' => $this->requireWorkingSchoolId(), 'class_level' => $classLevel])
        ->count();

    return ['count' => (int) $count];
}


public function actionStaffDirectory()
{
    $currentUser = Yii::$app->user->identity;

    if (!in_array($currentUser->role, ['SUPER_ADMIN', 'SCHOOL_ADMIN'], true)) {
        throw new \yii\web\ForbiddenHttpException('You are not authorized to view staff records.');
    }

    $schoolId = $this->requireWorkingSchoolId();

    $query = User::find()
        ->where(['school_id' => $schoolId])
        ->andWhere(['!=', 'role', 'SUPER_ADMIN'])
        ->orderBy(['status' => SORT_ASC, 'username' => SORT_ASC]);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 25],
    ]);

    return $this->render('staff-directory', [
        'dataProvider' => $dataProvider,
        'currentUserRole' => $currentUser->role,
    ]);
}

public function actionUpdateStaffStatus($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $currentUser = Yii::$app->user->identity;

    if (!in_array($currentUser->role, ['SUPER_ADMIN', 'SCHOOL_ADMIN'], true)) {
        throw new \yii\web\ForbiddenHttpException();
    }
    

    $staff = User::findOne($id);
    if (!$staff) {
        return ['success' => false, 'message' => 'Staff record not found.'];
    }
    if ($staff->id === $currentUser->id) {
    return ['success' => false, 'message' => 'You cannot change your own employment status.'];
}

    if ($currentUser->role !== 'SUPER_ADMIN' && $staff->school_id !== $currentUser->school_id) {
        throw new \yii\web\ForbiddenHttpException('You cannot manage staff outside your school.');
    }

    if ($staff->role === 'SUPER_ADMIN') {
        return ['success' => false, 'message' => 'Super admin accounts cannot be modified here.'];
    }

    $newStatus = Yii::$app->request->post('status');
    if (!in_array($newStatus, ['ACTIVE', 'INACTIVE', 'ON_LEAVE'], true)) {
        return ['success' => false, 'message' => 'Invalid status.'];
    }

    $staff->status = $newStatus;
    if (!$staff->save(false, ['status'])) {
        return ['success' => false, 'message' => 'Failed to update status.'];
    }

    // If they're no longer active, free up any canteen device they were holding
    if ($newStatus !== 'ACTIVE') {
        Yii::$app->db->createCommand()->update(
            'pos_devices',
            ['assigned_staff_id' => null],
            ['assigned_staff_id' => $staff->id]
        )->execute();
    }

    return ['success' => true, 'message' => "Status updated to {$newStatus}."];
}

public function actionRemoveStaff($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $currentUser = Yii::$app->user->identity;

    if (!in_array($currentUser->role, ['SUPER_ADMIN', 'SCHOOL_ADMIN'], true)) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $staff = User::findOne($id);
    if (!$staff) {
        return ['success' => false, 'message' => 'Staff record not found.'];
    }

    if ($currentUser->role !== 'SUPER_ADMIN' && $staff->school_id !== $currentUser->school_id) {
        throw new \yii\web\ForbiddenHttpException('You cannot manage staff outside your school.');
    }

    if ($staff->id === $currentUser->id) {
    return ['success' => false, 'message' => 'You cannot change your own employment status.'];
}

    if ($staff->role === 'SUPER_ADMIN') {
        return ['success' => false, 'message' => 'Super admin accounts cannot be removed here.'];
    }

    $staff->status = 'TERMINATED';
    if (!$staff->save(false, ['status'])) {
        return ['success' => false, 'message' => 'Failed to remove staff member.'];
    }

    Yii::$app->db->createCommand()->update(
        'pos_devices',
        ['assigned_staff_id' => null],
        ['assigned_staff_id' => $staff->id]
    )->execute();

    return ['success' => true, 'message' => 'Staff member removed and access revoked.'];
}

// Called from the existing parent-facing student dashboard, by the parent themselves
public function actionSponsorToggle($code)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $student = Students::findOne(['payment_code' => $code]);

    if (!$student) {
        return ['success' => false, 'message' => 'Student not found.'];
    }

    $enable = Yii::$app->request->post('enable') === '1';

    if ($enable && empty($student->sponsor_code)) {
        $student->sponsor_code = $student->generateSponsorCode();
    }

    $student->sponsorship_enabled = $enable;

    if (!$student->save(false, ['sponsorship_enabled', 'sponsor_code'])) {
        return ['success' => false, 'message' => 'Failed to update sponsorship setting.'];
    }

    return [
        'success' => true,
        'sponsorship_enabled' => $student->sponsorship_enabled,
        'sponsor_code' => $student->sponsor_code,
        'sponsor_url' => $student->sponsorship_enabled
            ? Url::toRoute(['site/sponsor', 'code' => $student->sponsor_code], true)
            : null,
    ];
}

// Public page — no login required, minimal info only
public function actionSponsor($code)
{
    $student = Students::findOne(['sponsor_code' => $code, 'sponsorship_enabled' => true]);

    if (!$student) {
        throw new \yii\web\NotFoundHttpException('This sponsorship link is invalid or no longer active.');
    }

    return $this->render('sponsor', [
        'firstName' => explode(' ', trim($student->name))[0] ?? $student->name,
        'classLevel' => $student->class_level,
        'sponsorCode' => $code,
    ]);
}

public function actionSponsorTopup($code)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;

    if (!$request->isPost) {
        return ['success' => false, 'message' => 'Bad Request.'];
    }
      if ($this->tooManyAttempts('sponsor_topup', 5, 60)) {
        Yii::$app->response->statusCode = 429;
        return ['success' => false, 'message' => 'Too many attempts. Please wait a moment and try again.'];
    }

    $idempotencyKey = trim((string) $request->post('idempotency_key'));

    if (empty($idempotencyKey)) {
        return ['success' => false, 'message' => 'Missing request identifier. Please refresh and try again.'];
    }

    $existing = Transactions::findOne(['idempotency_key' => $idempotencyKey]);
    if ($existing) {
        // Already processed — return the original outcome, don't charge again.
        return [
            'success' => $existing->status === 'SUCCESS',
            'message' => $existing->status === 'SUCCESS'
                ? 'Thank you! Your gift has been added to their wallet.'
                : 'This transaction could not be completed.',
        ];
    }

    $amount = (float) $request->post('amount');
    $sponsorName = trim((string) $request->post('sponsor_name'));

    $maxPerTransaction = 50000;   // UGX — tune per school
$maxPerStudentPerDay = 100000;

if ($amount <= 0 || $amount > $maxPerTransaction) {
    return ['success' => false, 'message' => 'Enter a valid amount (up to UGX ' . number_format($maxPerTransaction) . ' per gift).'];
}

    $student = Students::findOne(['sponsor_code' => $code, 'sponsorship_enabled' => true]);
    if (!$student) {
        return ['success' => false, 'message' => 'This sponsorship link is invalid or no longer active.'];
    }

    $startOfDay = date('Y-m-d 00:00:00');
    $endOfDay = date('Y-m-d 23:59:59');

    $givenToday = (float) Transactions::find()
        ->where(['student_id' => $student->id, 'transaction_type' => 'SPONSOR_TOPUP'])
        ->andWhere(['between', 'created_at', $startOfDay, $endOfDay])
        ->sum('amount');

   if ($givenToday + $amount > $maxPerStudentPerDay) {
    return ['success' => false, 'message' => 'This student has reached today\'s sponsorship limit. Please try again tomorrow.'];
}

    $dbTransaction = Yii::$app->db->beginTransaction();
    try {
        $student->swallet_balance += $amount;
        if (!$student->save(false, ['swallet_balance'])) {
            throw new \Exception('Failed to credit wallet.');
        }

        $ledger = new Transactions();
        $ledger->student_id = $student->id;
        $ledger->school_id = $student->school_id;
        $ledger->device_id = null; // not a terminal-originated transaction
        $ledger->amount = $amount;
        $ledger->transaction_type = 'SPONSOR_TOPUP';
        $ledger->payment_channel = 'SPONSOR_WEB';
        $ledger->external_reference = 'SPN_' . strtoupper(uniqid());
        $ledger->status = 'SUCCESS';
        $ledger->sponsor_name = $sponsorName !== '' ? $sponsorName : null;

        if (!$ledger->save()) {
            throw new \Exception('Failed to record sponsorship transaction.');
        }

        $dbTransaction->commit();

        return ['success' => true, 'message' => 'Thank you! Your gift has been added to their wallet.'];

    } catch (\Exception $e) {
        $dbTransaction->rollBack();
        return ['success' => false, 'message' => 'Something went wrong. Please try again.'];
    }
}

public function actionSponsorLookup()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
     if ($this->tooManyAttempts('sponsor_lookup', 8, 60)) {
        Yii::$app->response->statusCode = 429;
        return ['success' => false, 'message' => 'Too many attempts. Please wait a moment and try again.'];
    }
    $code = trim((string) Yii::$app->request->post('payment_code'));

    if (empty($code)) {
        return ['success' => false, 'message' => 'Enter a payment code to look up.'];
    }

$student = Students::findOne(['payment_code' => $code, 'sponsorship_enabled' => true])
    ?? Students::findOne(['sponsor_code' => $code, 'sponsorship_enabled' => true]);
    if (!$student) {
        return ['success' => false, 'message' => 'No sponsorship-enabled student found with that code. Double check the code with the family, or they may not have sponsorship enabled.'];
    }

    return [
        'success' => true,
        'first_name' => explode(' ', trim($student->name))[0] ?: $student->name,
        'class_level' => $student->class_level,
        'sponsor_url' => Url::toRoute(['site/sponsor', 'code' => $student->sponsor_code], true),
    ];
}

}