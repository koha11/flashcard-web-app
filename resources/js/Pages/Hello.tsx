// resources/js/Pages/Hello.tsx
import { Head } from '@inertiajs/react';

export default function Hello({ msg }: { msg: string }) {
  return (
    <>
      <Head title="Hello" />
      <h1 className="text-2xl font-bold">{msg}</h1>
      
    </>
  );
}
