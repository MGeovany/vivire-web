import { SUPABASE_URL, SUPABASE_ANON_KEY } from '../config.js';

let _client = null;

export function getClient() {
  if (!_client) {
    if (!SUPABASE_URL || !SUPABASE_ANON_KEY) throw new Error('Missing Supabase config');
    _client = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
  }
  return _client;
}
