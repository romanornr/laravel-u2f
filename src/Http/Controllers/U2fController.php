<?php
namespace Certly\U2f\Http\Controllers;
use App\Http\Controllers\Controller;
use Certly\U2f\U2f as LaravelU2f;
use Exception;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;

class U2fController extends Controller
{
    /**
     * @var LaravelU2f
     */
    protected $u2f;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param LaravelU2f $u2f
     * @param Config     $config
     */
    public function __construct(LaravelU2f $u2f, Config $config)
    {
        $this->u2f = $u2f;
        $this->config = $config;
    }

    /**
     * @param  Request $request
     * @return mixed
     */
    public function registerData(Request $request)
    {
        $user = $request->user();
        list($req, $sigs) = $this->u2f->getRegisterData($user);
        event('u2f.register.data', ['user' => $user]);
        $request->session()->put('u2f.registerData', $req);
        return view($this->config->get('u2f.register.view'))
            ->with('currentKeys', $sigs)
            ->with('registerData', $req);
    }

    /**
     * @param  Request $request [Get current $user via $request]
     * @return mixed
     */
    public function register(Request $request)
    {
        $user = $request->user();
        try {
            $key = $this->u2f->doRegister($user, session()->get('u2f.registerData'), json_decode($request->input('register')));
            event('u2f.register', ['u2fKey' => $key, 'user' => $user]);
            $request->session()->forget('u2f.registerData');
            if ($this->config->get('u2f.register.postSuccessRedirectRoute')) {
                return redirect()->route($this->config->get('u2f.register.postSuccessRedirectRoute'));
            }
            return redirect('/');
        } catch (Exception $e) {
            return redirect()->action('U2fController@registerData');
        }
    }

    /**
     * @param  Request $request
     * @return mixed
     */
    public function authData(Request $request)
    {
        $user = $request->user();
        if ($this->u2f->check()) return $this->redirectAfterSuccessAuth();

        $req = $this->u2f->getAuthenticateData($user);
        event('u2f.authentication.data', ['user' => $user]);
        $request->session()->put('u2f.authenticationData', $req);

        return view($this->config->get('u2f.authenticate.view'))
            ->with('authenticationData', $req);
    }

    /**
     * @param  Request $request
     * @return mixed
     */
    public function auth(Request $request)
    {
        $user = $request->user();
        try {
            $key = $this->u2f->doAuthenticate($user, session()->get('u2f.authenticationData'), json_decode($request->input('authentication')));
            event('u2f.authentication', ['u2fKey' => $key, 'user' => $user]);
            $request->session()->forget('u2f.authenticationData');
            return $this->redirectAfterSuccessAuth();
        } catch (Exception $e) {
            $request->session()->flash('error', $e->getMessage());
            return redirect()->action('U2fController@authData');
        }
    }

    /**
     * @return mixed
     */
    protected function redirectAfterSuccessAuth()
    {
        if (strlen($this->config->get('u2f.authenticate.postSuccessRedirectRoute'))) {
            return redirect()->intended($this->config->get('u2f.authenticate.postSuccessRedirectRoute'));
        }
        return redirect()->intended('/');
    }
}