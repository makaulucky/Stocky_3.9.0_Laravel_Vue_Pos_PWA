import axios from 'axios'
import store from '../store/index.js'

export default class Auth {

    login(token) {
        store.dispatch('setLoginCred', {
            token: token,
        });
    }

    setAuthToken(token) {
        let tokenIsSet = axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
    }


    // checks for login status, returns boolean
    check() {
        return !!store.state.auth.token;
    }
}