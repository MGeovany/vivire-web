import { getClient } from '../lib/supabase.js';

export class AuthService {
  constructor() {
    this._client = getClient();
  }

  /**
   * Subscribe to auth state changes.
   * Calls cb(user | null) whenever the session changes.
   * @param {(user: object|null) => void} cb
   * @returns {{ data: { subscription: { unsubscribe(): void } } }}
   */
  onAuthStateChange(cb) {
    return this._client.auth.onAuthStateChange((_e, session) => cb(session?.user ?? null));
  }

  /**
   * Create a new user account.
   * @param {string} name
   * @param {string} email
   * @param {string} password
   * @throws {Error} with a humanized message on failure
   */
  async signUp(name, email, password) {
    const { error } = await this._client.auth.signUp({
      email,
      password,
      options: { data: { name } },
    });
    if (error) throw new Error(this._humanize(error.message));
  }

  /**
   * Sign in with email and password.
   * @param {string} email
   * @param {string} password
   * @throws {Error} with a humanized message on failure
   */
  async signIn(email, password) {
    const { error } = await this._client.auth.signInWithPassword({ email, password });
    if (error) throw new Error(this._humanize(error.message));
  }

  /**
   * Sign out the current user.
   */
  async signOut() {
    await this._client.auth.signOut();
  }

  /**
   * Return the current session's access token, or null if not authenticated.
   * @returns {Promise<string|null>}
   */
  async getAccessToken() {
    const { data: { session } } = await this._client.auth.getSession();
    return session?.access_token ?? null;
  }

  /**
   * Convert a Supabase auth error message into a user-friendly Spanish string.
   * @param {string} msg
   * @returns {string}
   */
  _humanize(msg) {
    if (/invalid login/i.test(msg))       return 'Email o contraseña incorrectos.';
    if (/already registered/i.test(msg))  return 'Ya existe una cuenta con ese email.';
    if (/password should be/i.test(msg))  return 'La contraseña debe tener al menos 6 caracteres.';
    if (/invalid email/i.test(msg))       return 'El email no es válido.';
    if (/email not confirmed/i.test(msg)) return 'Confirma tu email antes de entrar.';
    return msg;
  }
}
