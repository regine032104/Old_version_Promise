This project uses Tailwind CSS (v4) and PostCSS to build a single compiled stylesheet.

Prerequisites
- Node.js (14+ recommended) and npm.

Install dependencies
Run from project root:

```powershell
npm install
```

Build CSS (one-off)

- Using npm (works in cmd.exe; PowerShell may block npm.ps1 scripts if execution policy is restricted):

```powershell
# In PowerShell you may prefer to run via cmd to avoid script execution policy issues
cmd /c "npm run build:css"

# Or run directly (may require changing execution policy):
npm run build:css
```

Watch (development)

```powershell
# Starts Tailwind CLI in watch mode and writes to src/output.css
npm run dev
```

Notes
- The compiled file the app references is `src/output.css`. Ensure the server serves that path (the layout links `../output.css` from some templates; the layout in this repo expects `src/output.css`).
- If PowerShell errors with `cannot be loaded because running scripts is disabled`, either run the build from `cmd` or update your PowerShell execution policy (see Microsoft docs).