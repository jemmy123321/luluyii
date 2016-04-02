<?php
namespace app\modules\user\models;
use yii\base\Model;
use app\models\config\EmailConfig;

class FindPasswordForm extends Model
{
    public $email;

    public function init()
    {
        parent::init();
        \Yii::$app->set('mailer', [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => EmailConfig::getKey('smtpHost'),
                'username' => EmailConfig::getKey('smtpUser'),
                'password' => EmailConfig::getKey('smtpPassword'),
                'port' => EmailConfig::getKey('smtpPort'),
                'encryption' => 'tls',
        ]]);
    }

    public function rules()
    {
        return [
            ['email','filter','filter'=>'trim'],
            ['email','required'],
            ['email','email'],
            //检测用户输入的邮箱是否存在于数据库
            //用户是服务器验证，需要在控制器中添加$model->validate()进行服务器验证
            //当然，不管如果在客户端先验证了，最好也在服务器端再一次进行验证
            ['email','exist',
                'targetClass' => 'app\modules\user\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => '用户不存在',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email'=>'输入邮箱',
        ];
    }

    public function sendEmail()
    {
        $user = User::findOne([
            'status'=>User::STATUS_ACTIVE,
            'email'=>$this->email
        ]);

        if($user){
            //如果重置密码的令牌过期了，就重新生成令牌
            //一开始令牌为空，是过期的，所以一开始会生成令牌
            if(!User::isPasswordResetTokenValid($user->password_reset_token)){
                $user->generatePasswordResetToken();
            }

            //$user->save()保存密码令牌
            if($user->save()){
                return  \Yii::$app->mailer->compose('passwordResetToken', ['user' => $user])
                    ->setFrom([EmailConfig::getKey('smtpUser') => \Yii::$app->name])
                    ->setTo($this->email)
                    ->setSubject(\Yii::$app->name.'重置密码 ' )
                    ->send();
            }
        }
        return false;
    }
}
?>