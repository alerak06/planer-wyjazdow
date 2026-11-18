import type { Balance, CostLine, Transfer } from "@/lib/types";

const round2 = (n: number) => Math.round(n * 100) / 100;

// ile kto zaplacil minus ile powinien (jego czesc kosztow, w ktorych bral udzial)
export function computeBalances(costLines: CostLine[], memberIds: string[]): Balance[] {
  const paid: Record<string, number> = {};
  const owed: Record<string, number> = {};
  for (const id of memberIds) {
    paid[id] = 0;
    owed[id] = 0;
  }

  for (const line of costLines) {
    if (line.paidBy in paid) paid[line.paidBy] += line.amount;

    const participants = line.participantIds.filter((id) => id in owed);
    if (participants.length === 0) continue;
    const share = line.amount / participants.length;
    for (const id of participants) owed[id] += share;
  }

  return memberIds.map((id) => ({
    userId: id,
    paid: round2(paid[id] ?? 0),
    owed: round2(owed[id] ?? 0),
    net: round2((paid[id] ?? 0) - (owed[id] ?? 0)),
  }));
}

// najwiekszy dluznik placi najwiekszemu wierzycielowi, az sie wszystko wyzeruje.
// nie zawsze daje absolutne minimum przelewow, ale przy kilku osobach roznicy nie ma
export function simplifyDebts(balances: Balance[]): Transfer[] {
  const creditors = balances
    .filter((b) => b.net > 0.005)
    .map((b) => ({ id: b.userId, amount: b.net }))
    .sort((a, b) => b.amount - a.amount);
  const debtors = balances
    .filter((b) => b.net < -0.005)
    .map((b) => ({ id: b.userId, amount: -b.net }))
    .sort((a, b) => b.amount - a.amount);

  const transfers: Transfer[] = [];
  let i = 0;
  let j = 0;

  while (i < debtors.length && j < creditors.length) {
    const debtor = debtors[i];
    const creditor = creditors[j];
    const amount = round2(Math.min(debtor.amount, creditor.amount));

    if (amount > 0.005) {
      transfers.push({ from: debtor.id, to: creditor.id, amount });
    }

    debtor.amount = round2(debtor.amount - amount);
    creditor.amount = round2(creditor.amount - amount);

    if (debtor.amount <= 0.005) i++;
    if (creditor.amount <= 0.005) j++;
  }

  return transfers;
}
