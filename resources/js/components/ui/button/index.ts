import { cva, type VariantProps } from 'class-variance-authority'

export { default as Button } from './Button.vue'

export const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*=\'size-\'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
  {
    variants: {
      variant: {
        default:
          // primary: use concrete Tailwind base colors for reliable rendering
          // Light: blue-600 background with white text, Dark: slightly lighter blue
          'bg-blue-600 text-white shadow-xs hover:bg-blue-700 focus-visible:ring-blue-200 dark:bg-blue-500 dark:hover:bg-blue-600',
        destructive:
          // destructive: red style (leave as-is with reliable base colors)
          'bg-red-600 text-white shadow-xs hover:bg-red-700 focus-visible:ring-red-200 dark:bg-red-500 dark:hover:bg-red-400',
        outline:
          // outline: transparent background with neutral borders, light and dark mode
          'bg-transparent border border-neutral-200 text-neutral-900 shadow-xs hover:bg-neutral-50 hover:text-neutral-900 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-100',
        secondary:
          // secondary: subdued neutral/gray button that adapts to dark mode
          'bg-neutral-100 text-neutral-900 shadow-xs hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-100 dark:hover:bg-neutral-600',
        ghost:
          // ghost: minimal background, keep text color appropriate
          'bg-transparent text-neutral-700 hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-700',
        link:
          // link: text-only using blue as the interactive color
          'text-blue-600 underline-offset-4 hover:underline dark:text-blue-400',
      },
      size: {
        default: 'h-9 px-4 py-2 has-[>svg]:px-3',
        sm: 'h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5',
        lg: 'h-10 rounded-md px-6 has-[>svg]:px-4',
        icon: 'size-9',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
