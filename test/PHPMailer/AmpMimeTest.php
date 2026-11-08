<?php

namespace PHPMailer\Test\PHPMailer;

use PHPMailerAMP\PHPMailerAMP\PHPMailerAMP;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPMailerAMP\PHPMailerAMP\PHPMailerAMP
 */
class AmpMimeTest extends TestCase
{
    /**
     * @dataProvider attachmentCombinations
     *
     * @param bool $inline
     * @param bool $attachment
     */
    public function testAmpAlternativeWithAttachments($inline, $attachment)
    {
        $mail = $this->createMailer();
        $mail->Body = '<p>HTML fallback</p>';
        $mail->AltBody = 'Plain fallback';
        $mail->AmpBody = '<p>AMP content</p>';
        if ($inline) {
            $mail->addStringEmbeddedImage('image data', 'image@example.org', 'inline.png');
        }
        if ($attachment) {
            $mail->addStringAttachment('attachment data', 'file.txt');
        }

        self::assertTrue($mail->preSend());
        $outer = $this->splitEntity($mail->getSentMIMEMessage());
        if ($attachment) {
            $mixed = $this->multipartChildren($outer, 'mixed');
            self::assertCount(2, $mixed);
            self::assertStringContainsString('Content-Disposition: attachment;', $mixed[1][0]);
            $outer = $mixed[0];
        }

        $parts = $this->multipartChildren($outer, 'alternative');
        self::assertCount(3, $parts);
        self::assertStringContainsString('Content-Type: text/plain;', $parts[0][0]);
        self::assertStringContainsString('Content-Type: text/x-amp-html;', $parts[1][0]);
        self::assertSame('<p>AMP content</p>', trim($parts[1][1]));

        $html = $parts[2];
        if ($inline) {
            $related = $this->multipartChildren($html, 'related');
            self::assertCount(2, $related);
            self::assertStringContainsString('Content-ID: <image@example.org>', $related[1][0]);
            $html = $related[0];
        }
        self::assertStringContainsString('Content-Type: text/html;', $html[0]);
        self::assertSame('<p>HTML fallback</p>', trim($html[1]));
    }

    /**
     * @return array
     */
    public static function attachmentCombinations()
    {
        return [
            'no attachments' => [false, false],
            'inline only' => [true, false],
            'file only' => [false, true],
            'inline and file' => [true, true],
        ];
    }

    public function testMsgAmpProvidesFallbackWithoutHtml()
    {
        $mail = $this->createMailer();
        $mail->msgAMP('<p>Readable fallback</p>');
        self::assertTrue($mail->preSend());
        $parts = $this->multipartChildren($this->splitEntity($mail->getSentMIMEMessage()), 'alternative');
        self::assertCount(2, $parts);
        self::assertStringContainsString('Content-Type: text/plain;', $parts[0][0]);
        self::assertSame('Readable fallback', trim($parts[0][1]));
        self::assertStringContainsString('Content-Type: text/x-amp-html;', $parts[1][0]);
    }

    /**
     * @return PHPMailerAMP
     */
    private function createMailer()
    {
        $mail = new PHPMailerAMP(true);
        $mail->isSMTP();
        $mail->setFrom('sender@example.org');
        $mail->addAddress('recipient@example.org');

        return $mail;
    }

    /**
     * @param string $message
     *
     * @return array
     */
    private function splitEntity($message)
    {
        $parts = explode("\r\n\r\n", $message, 2);
        self::assertCount(2, $parts);

        return $parts;
    }

    /**
     * @param array  $entity
     * @param string $subtype
     *
     * @return array
     */
    private function multipartChildren($entity, $subtype)
    {
        $pattern = '/^Content-Type: multipart\/' . $subtype . ';\r\n[ \t]+boundary="([^"]+)"/mi';
        self::assertSame(1, preg_match($pattern, $entity[0], $matches));
        self::assertSame(1, preg_match_all('/^Content-Type:/mi', $entity[0], $headers));

        $boundary = preg_quote($matches[1], '/');
        $parts = preg_split('/^--' . $boundary . '(--)?\r\n/m', $entity[1], -1, PREG_SPLIT_DELIM_CAPTURE);
        self::assertSame('--', $parts[count($parts) - 2]);
        array_pop($parts);
        array_pop($parts);
        array_shift($parts);

        return array_map([$this, 'splitEntity'], $parts);
    }
}
