/**
 * Generate a short unique id.
 * @returns {string}
 */
export function uid() {
  return Math.random().toString(36).slice(2, 10) + Date.now().toString(36);
}

/**
 * Format a byte count into a human-readable string.
 * @param {number} bytes
 * @returns {string}
 */
export function formatFileSize(bytes) {
  if (!bytes) return '';
  if (bytes < 1024)        return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

/**
 * Return an emoji icon appropriate for a document file extension.
 * @param {string} name  File name (extension is extracted from the last segment)
 * @returns {string}
 */
export function docIcon(name) {
  const ext = name.split('.').pop().toLowerCase();
  if (['pdf'].includes(ext))                       return '📕';
  if (['doc', 'docx'].includes(ext))               return '📝';
  if (['xls', 'xlsx', 'csv'].includes(ext))        return '📊';
  if (['ppt', 'pptx'].includes(ext))               return '📋';
  if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return '🗜';
  return '📄';
}
