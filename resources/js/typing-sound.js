import { tiks } from '@rexa-developer/tiks';

let enabled = localStorage.getItem('vivire_sound') !== 'off';

tiks.init({
  theme: 'crisp',
  muted: !enabled,
  volume: 0.85,
  respectReducedMotion: true,
});

function play(key) {
  if (!enabled) return;
  const printable = (key && key.length === 1) || key === 'Backspace' || key === 'Enter';
  if (!printable) return;
  tiks.click();
}

function setEnabled(on) {
  enabled = on;
  localStorage.setItem('vivire_sound', on ? 'on' : 'off');
  if (on) tiks.unmute();
  else tiks.mute();
}

function isEnabled() {
  return enabled;
}

window.vivireTypingSound = { play, setEnabled, isEnabled };
