#!/usr/bin/env bash
# بیلدی ئەسێت + کۆمیت + پوش — دواتر گیتهەب خۆی دیپلۆی دەکات.
# بەکارهێنان:  ./deploy.sh "ناونیشانی گۆڕانکاری"
set -e

MSG="${1:-update}"

echo "📦 بیلدکردنی ئەسێتەکان..."
npm run build

echo "📤 ناردن بۆ گیتهەب..."
git add -A
git commit -m "$MSG"
git push origin main

echo "✅ نێردرا — دیپلۆیەکە لە گیتهەبدا دەستیپێکرد:"
echo "   https://github.com/Ibrahim-pti/Hemin-system/actions"
