<?php
namespace app\modules\user\models;
use yii\base\Model;

class ModifyPasswordForm extends Model
{
    public $old_password;
    public $new_password;
    public $renew_password;
    private $_user = false;

    public function rules()
    {
        return [
            [['old_password','new_password','renew_password'],'required'],
            [['new_password','renew_password'],'string','min'=>6],
            ['renew_password','compare','compareAttribute'=>'new_password','message'=>'两次输入的密码不一致'],
            ['old_password','validatePassword']
        ];
    }

    public function attributeLabels()
    {
        return [
            'old_password'=>'旧密码',
            'new_password'=>'新密码',
            'renew_password'=>'重复新密码',
        ];
    }

    public function validatePassword($attribute,$param)
    {
        if(!$this->hasErrors()){
            $user = $this->getUser();
        }
        if(!$user || !$user->validatePassword($this->old_password)){
            $this->addError($attribute,'密码错误');
        }
    }

    public function modifyPassword()
    {
        if ($this->validate()){
            $user = $this->getUser();
            $user->password = $this->new_password;
            return $user->save();
        }
    }

    //获取当前登录用户的信息
    public function getUser()
    {
        if($this->_user===false){
            $this->_user = \Yii::$app->user->identity;
        }
        return $this->_user;
    }

}
?>