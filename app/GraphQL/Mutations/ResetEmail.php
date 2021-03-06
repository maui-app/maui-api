<?php

namespace App\GraphQL\Mutations;

use Illuminate\Http\Request;
use App\Models\EmailReset;
use App\Models\User;
use App\Events\EmailChanged;
class ResetEmail
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */

    protected $request;

    public function __construct(Request $request) 
    {
        $this->request = $request;
    }

    public function __invoke($_, array $args)
    {
        $user = $this->request->user();
        date_default_timezone_set($user->timezone);
        $email = $args['email'];
        $token = $args['token'];

        $userWithEmail = User::where('email', $email)->first();

        if($userWithEmail) {
            return [
                'message' => 'user with email exists already',
                'errorId' => 'InvalidEmailAddress'
            ];
        }

        $reset = EmailReset::where('user_id', $user->id)
        ->where('token', $token)
        ->first();

        if(!$reset) {
            EmailReset::where('user_id', $user->id)->delete();
            return [
                'message' => 'invalid email reset token',
                'errorId' => 'InvalidEmailResetToken'
            ];
        }

        if(time() > $reset->expires_at) {
            EmailReset::where('user_id', $user->id)->delete();
            return [
                'message' => 'expired email reset token',
                'errorId' => 'InvalidEmailResetToken'
            ];
        }

        $reset->delete();
        $user->email = $email;
        $user->save();
        event(new EmailChanged($user));
        return [
            'message' => 'user email changed successfully',
            'user' => $user
        ];
    }   
}
