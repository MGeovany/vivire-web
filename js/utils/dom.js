/**
 * Create a DOM element with an optional className and innerHTML.
 * @param {string} tag
 * @param {string} [className]
 * @param {string} [html]
 * @returns {HTMLElement}
 */
export function el(tag, className, html) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (html !== undefined) node.innerHTML = html;
  return node;
}

/**
 * Display a brief toast notification at the bottom of the screen.
 * @param {string} msg
 */
export function showToast(msg) {
  let toast = document.getElementById('app-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'toast';
    toast.id = 'app-toast';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

/**
 * Move the caret to the end of a contenteditable element.
 * @param {HTMLElement} element
 */
export function placeCursorAtEnd(element) {
  const range = document.createRange();
  const sel   = window.getSelection();
  range.selectNodeContents(element);
  range.collapse(false);
  sel.removeAllRanges();
  sel.addRange(range);
}
