// resources/js/Pages/Demo/Index.tsx
import { Link, useForm } from "@inertiajs/react";
import { route } from "ziggy-js";

export default function Index() {
    const listUrl = route("demo.index");

    const { data, setData, post, processing, errors, reset } = useForm({
        content: "",
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route("demo.post-paragraph"), {
            onSuccess: () => reset("content"),
        });
    }

    return (
        <>
            <Link href={listUrl}>All posts</Link>

            <form onSubmit={submit} className="mt-4 space-y-3">
                <label htmlFor="content">Content</label>
                <textarea
                    id="content"
                    name="content"
                    rows={6}
                    value={data.content}
                    onChange={(e) => setData("content", e.target.value)}
                    className="w-full rounded border p-2"
                    placeholder="Type your paragraph..."
                />
                {errors.content && (
                    <div className="text-sm text-red-600">{errors.content}</div>
                )}

                <button
                    type="submit"
                    disabled={processing}
                    className="rounded bg-black px-3 py-2 text-white disabled:opacity-50"
                >
                    {processing ? "Creating..." : "Create"}
                </button>
            </form>
        </>
    );
}
