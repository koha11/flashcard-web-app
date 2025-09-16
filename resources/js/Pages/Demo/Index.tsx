// Example: resources/js/Pages/Posts/Index.tsx
import { route } from "ziggy-js";
import { Link, router } from "@inertiajs/react";

export default function Index() {
    const listUrl = route("demo.index"); // "/demo"

    function postParagraph(content: string) {
        router.post(route("demo.post-paragraph"), { content });
    }

    return (
        <>
            <Link href={listUrl}>All posts</Link>
            <button onClick={() => postParagraph("New content")}>Create</button>
        </>
    );
}
