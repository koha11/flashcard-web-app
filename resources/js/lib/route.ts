// resources/js/lib/route.ts
import baseRoute from 'ziggy-js';
import { Ziggy } from '@/ziggy'; // generated file

export function makeUrl(
  name: string,
  params?: Record<string, any>,
  absolute?: boolean,
  currentUrl?: string
) {
  // On SSR you may want to override Ziggy.url with the incoming request URL/host
  const cfg = { ...Ziggy, url: currentUrl ?? Ziggy.url };
  return baseRoute(name, params, absolute, cfg);
}
