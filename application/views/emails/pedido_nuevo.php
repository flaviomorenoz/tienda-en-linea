<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cuerpo HTML del correo "Nuevo pedido" que se envía a la tienda.
 *
 * Variables recibidas desde Pago::_enviar_correo_pedido():
 *   $id_pedido, $tienda, $fecha, $pedido, $items, $total, $archivo, $archivo_url, $moneda
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Nuevo pedido #<?php echo $id_pedido; ?></title>
</head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial, Helvetica, sans-serif; color:#222222;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="width:620px; background:#ffffff; border:1px solid #e5e5e5; border-radius:8px;">
                    <tr>
                        <td style="background:#111111; color:#ffffff; padding:18px 24px; border-radius:8px 8px 0 0;">
                            <div style="font-size:18px; font-weight:bold;"><?php echo htmlspecialchars($tienda, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div style="font-size:12px; color:#cccccc; margin-top:4px;">Nuevo pedido recibido en la tienda en línea</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">

                            <p style="margin:0 0 16px; font-size:16px; font-weight:bold;">
                                Pedido #<?php echo $id_pedido; ?>
                                <span style="font-weight:normal; color:#777777; font-size:13px;">&nbsp;&mdash;&nbsp;<?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></span>
                            </p>

                            <!-- Datos del cliente -->
                            <table role="presentation" width="100%" cellpadding="5" cellspacing="0" style="border-collapse:collapse; font-size:13px; margin-bottom:18px;">
                                <tr>
                                    <td width="150" style="color:#777777; border-bottom:1px solid #f0f0f0;">Cliente</td>
                                    <td style="border-bottom:1px solid #f0f0f0;"><strong><?php echo htmlspecialchars(isset($pedido['nombres']) ? $pedido['nombres'] : '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="color:#777777; border-bottom:1px solid #f0f0f0;">DNI</td>
                                    <td style="border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars(isset($pedido['dni']) ? $pedido['dni'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:#777777; border-bottom:1px solid #f0f0f0;">Celular</td>
                                    <td style="border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars(isset($pedido['celular']) ? $pedido['celular'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:#777777; border-bottom:1px solid #f0f0f0;">Dirección de envío</td>
                                    <td style="border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars(isset($pedido['direccion_envio']) ? $pedido['direccion_envio'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:#777777; border-bottom:1px solid #f0f0f0;">Observaciones</td>
                                    <td style="border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars(isset($pedido['observaciones']) ? $pedido['observaciones'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            </table>
                            <!-- Detalle del pedido -->
                            <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:13px;">
                                <tr style="background:#f7f7f7;">
                                    <th align="left"   style="border-bottom:1px solid #dddddd;">Producto</th>
                                    <th align="center" style="border-bottom:1px solid #dddddd;">Talla</th>
                                    <th align="center" style="border-bottom:1px solid #dddddd;">Cant.</th>
                                    <th align="right"  style="border-bottom:1px solid #dddddd;">P. unit.</th>
                                    <th align="right"  style="border-bottom:1px solid #dddddd;">Subtotal</th>
                                </tr>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td style="border-bottom:1px solid #eeeeee;"><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td align="center" style="border-bottom:1px solid #eeeeee;"><?php echo htmlspecialchars($item['talla'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td align="center" style="border-bottom:1px solid #eeeeee;"><?php echo (int)$item['cantidad']; ?></td>
                                    <td align="right"  style="border-bottom:1px solid #eeeeee;"><?php echo $moneda . ' ' . number_format($item['precio'], 2); ?></td>
                                    <td align="right"  style="border-bottom:1px solid #eeeeee;"><?php echo $moneda . ' ' . number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="4" align="right" style="padding-top:10px; font-weight:bold;">Total</td>
                                    <td align="right" style="padding-top:10px; font-weight:bold; font-size:15px;"><?php echo $moneda . ' ' . number_format($total, 2); ?></td>
                                </tr>
                            </table>

                            <?php if ($archivo_url !== ''): ?>
                            <p style="margin:18px 0 0; font-size:13px;">
                                <strong>Comprobante de pago:</strong>
                                <a href="<?php echo htmlspecialchars($archivo_url, ENT_QUOTES, 'UTF-8'); ?>" style="color:#0d6efd;"><?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?></a>
                            </p>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px; background:#fafafa; border-top:1px solid #eeeeee; font-size:11px; color:#888888; border-radius:0 0 8px 8px;">
                            Correo automático generado por <?php echo htmlspecialchars($tienda, ENT_QUOTES, 'UTF-8'); ?>. No responder a este mensaje.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
