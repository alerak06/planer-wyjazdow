import type { AvailabilityStatus, DayStats } from "@/lib/types";

// punkty (moge = 2, moze = 1, nie moge = 0) sa tylko do sortowania,
// uzytkownik ich nie widzi, widzi tylko ile osob moze danego dnia
export function aggregateDayStats(
  rows: { date: string; status: AvailabilityStatus }[]
): Record<string, DayStats> {
  const stats: Record<string, DayStats> = {};

  for (const row of rows) {
    if (!stats[row.date]) {
      stats[row.date] = { available: 0, maybe: 0, points: 0 };
    }
    if (row.status === "available") {
      stats[row.date].available++;
      stats[row.date].points += 2;
    } else if (row.status === "maybe") {
      stats[row.date].maybe++;
      stats[row.date].points += 1;
    }
  }

  return stats;
}

export function getBestDates(dayStats: Record<string, DayStats>, limit = 5): string[] {
  return Object.entries(dayStats)
    .filter(([, s]) => s.points > 0)
    .sort((a, b) => b[1].points - a[1].points || a[0].localeCompare(b[0]))
    .slice(0, limit)
    .map(([date]) => date);
}

const WEEKDAY_LABELS = ["Pn", "Wt", "Śr", "Cz", "Pt", "So", "Nd"];
const MONTH_LABELS = [
  "Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec",
  "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień",
];

export { WEEKDAY_LABELS, MONTH_LABELS };

// dni miesiaca do kalendarza, tydzien od poniedzialku, null = puste pole
export function buildMonthGrid(year: number, month: number): (string | null)[] {
  const first = new Date(year, month - 1, 1);
  const daysInMonth = new Date(year, month, 0).getDate();
  let startDay = first.getDay(); // 0 = niedziela
  startDay = startDay === 0 ? 7 : startDay;

  const cells: (string | null)[] = [];
  for (let i = 1; i < startDay; i++) cells.push(null);
  for (let d = 1; d <= daysInMonth; d++) {
    cells.push(`${year}-${String(month).padStart(2, "0")}-${String(d).padStart(2, "0")}`);
  }
  while (cells.length % 7 !== 0) cells.push(null);
  return cells;
}
