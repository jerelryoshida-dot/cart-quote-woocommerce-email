# Git Hooks Setup

This project uses a pre-push hook to prevent local-only files from being pushed to any remote branch.

## Local-Only Files

The following files/folders are restricted to local-only and will be blocked from being pushed:

- `.gitignore`
- `.github/`

## Installation

After cloning this repository, install the git hooks by running:

```bash
# Copy the hook template to your local .git/hooks folder
cp .githooks/pre-push .git/hooks/pre-push

# Make the hook executable
chmod +x .git/hooks/pre-push
```

### One-liner (Git Bash / Linux / macOS)

```bash
cp .githooks/pre-push .git/hooks/pre-push && chmod +x .git/hooks/pre-push
```

### Windows (PowerShell)

```powershell
Copy-Item .githooks/pre-push .git/hooks/pre-push
```

## How It Works

When you run `git push`, the pre-push hook will:

1. Check all commits being pushed for any changes to `.gitignore` or `.github/`
2. If any restricted files are found, the push is aborted with an error message
3. If no restricted files are found, the push proceeds normally

## Bypass the Hook (Not Recommended)

If you absolutely need to push restricted files (not recommended), you can bypass the hook:

```bash
git push --no-verify
```

## Adding More Local-Only Files

To add more files/folders to the restricted list, edit `.githooks/pre-push` and update the `LOCAL_ONLY_FILES` array:

```bash
LOCAL_ONLY_FILES=(
    ".gitignore"
    ".github/"
    "your-new-file-or-folder"
)
```

Then copy the updated hook to your local `.git/hooks/` folder.

## Troubleshooting

### Hook not running

- Ensure the hook file is executable: `chmod +x .git/hooks/pre-push`
- Check the file has Unix line endings (LF), not Windows (CRLF)
- Verify the hook file is at `.git/hooks/pre-push` (no file extension)

### Push blocked unexpectedly

- Check if you've accidentally committed changes to `.gitignore` or `.github/`
- Use `git log --name-only` to see which files were changed in recent commits
