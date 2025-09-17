// components/VocabList.tsx
import React from "react";

export type FlashcardItem = { front: string; back: string };

type Props = {
    items: FlashcardItem[];
    title?: string;
    searchable?: boolean;
    className?: string;
};

export default function FlashcardList({
    items,
    title = "Vocabulary",
    searchable = true,
    className = "",
}: Props) {
    const [q, setQ] = React.useState("");

    const filtered = React.useMemo(() => {
        if (!q.trim()) return items;
        const s = q.toLowerCase();
        return items.filter(
            (it) =>
                it.front.toLowerCase().includes(s) ||
                it.back.toLowerCase().includes(s)
        );
    }, [items, q]);

    return (
        <section className={className}>
            <header className="mb-3">
                <h2 className="text-lg font-semibold">{title}</h2>
                {searchable && (
                    <input
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder="Search..."
                        className="mt-2 w-full rounded border px-3 py-2"
                    />
                )}
            </header>

            {filtered.length === 0 ? (
                <p className="text-sm text-gray-500">No items.</p>
            ) : (
                <ul className="divide-y rounded border">
                    {filtered.map((it, idx) => (
                        <li key={idx} className="grid grid-cols-2 gap-3 p-3">
                            <span className="font-medium">{it.front}</span>
                            <span className="text-gray-700">{it.back}</span>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
