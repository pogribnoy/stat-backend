Call SystemnsUpdate()

Sub SystemnsUpdate()
    On Error Resume Next
    Dim Request
    Dim URL

    URL = "https://system-ns.com/api?type=dynamic&domain=mydomain.system-ns.net&command=set&token=880078764367979fe765c0fa3f4efff1"

    Set Request = CreateObject("Microsoft.XMLHTTP")
    Request.open "GET", URL , false
    Request.Send

    Set Request = Nothing
End Sub