const ES_DAYS = [
  'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado',
];

const ES_MONTHS = [
  'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

/**
 * Format a Date as "Jueves, 29 de mayo de 2026"
 * @param {Date} date
 * @returns {string}
 */
export function formatDate(date) {
  const day   = ES_DAYS[date.getDay()];
  const d     = date.getDate();
  const month = ES_MONTHS[date.getMonth()];
  const year  = date.getFullYear();
  return `${day}, ${d} de ${month} de ${year}`;
}

/**
 * Format as "Mayo 29, 2026" (for year section headings)
 * @param {Date} date
 * @returns {string}
 */
export function formatDateShort(date) {
  const month = ES_MONTHS[date.getMonth()];
  const cap   = month.charAt(0).toUpperCase() + month.slice(1);
  return `${cap} ${date.getDate()}, ${date.getFullYear()}`;
}

/**
 * Returns YYYY-MM-DD string in local time (no UTC shift)
 * @param {Date} date
 * @returns {string}
 */
export function toISODate(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

/**
 * Returns [date+1yr, date+2yr, date+3yr]
 * @param {Date} today
 * @returns {Date[]}
 */
export function getYearDates(today) {
  return [1, 2, 3].map(n => {
    const d = new Date(today);
    d.setFullYear(d.getFullYear() + n);
    return d;
  });
}
